/// <reference path='../components/common/Commits.d.ts' />
/// <reference path='../interfaces/State.d.ts' />

import { action, observable } from 'mobx';
import * as ReactRouter from 'react-router';
import * as request from 'superagent';

import config from '../config/config';
import * as WpApi from '../services/WpApi';
import { indexOf } from '../utils/CommitUtils';
import { getErrorMessage } from './utils';

const routes = config.routes;

class AppStore {
  @observable page: number;
  @observable pages: number[] = [];
  @observable query: string = '';
  @observable commits: Commit[] = [];
  @observable selectedCommits: Commit[] = [];
  @observable lastSelectedCommit: Commit = null;
  @observable message: InfoMessage = null;
  @observable isLoading: boolean = true;
  @observable displayServicePanel: boolean = false;
  @observable displayWelcomePanel: boolean = false;
  @observable displayUpdateNotice: boolean = false;
  @observable isDirtyWorkingDirectory: boolean = false;
  @observable progress: number = 100;

  private refreshInterval;
  private router: ReactRouter.Context;

  constructor() {
    this.refreshInterval = setInterval(this.checkUpdate, 10 * 1000);
  }

  @action
  private updateProgress = (e: {percent: number}) => {
    this.progress = e.percent;
  };

  @action
  private setLoading = () => {
    this.isLoading = true;
    this.progress = 0;
  };

  @action
  private wpUndoRollback = (name: string, query: any) => {
    this.setLoading();

    WpApi
      .get(name)
      .query(query)
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        if (err) {
          this.message = getErrorMessage(res, err);
          this.isLoading = false;
        } else {
          this.router.transitionTo(routes.home);
          document.location.reload();
        }
      });
  };

  @action
  private wpCommitDiscardEnd = (successMessage: string) => {
    return (err: any, res: request.Response) => {
      if (err) {
        this.message = getErrorMessage(res, err);
      } else {
        this.isDirtyWorkingDirectory = false;
        this.message = {
          code: 'updated',
          message: successMessage,
        };
        this.fetchCommits();
      }
      return !err;
    };
  };

  @action
  updatePage = (page: string) => {
    this.page = (parseInt(page, 10) - 1) || 0;
  };

  @action
  setRouter = (router: ReactRouter.Context) => {
    this.router = router;
  };

  @action
  fetchCommits = (page = this.page) => {
    this.setLoading();

    if (page < 1) {
      this.router.transitionTo(routes.home);
    }

    WpApi
      .get('commits')
      .query({
        page: page,
        query: encodeURIComponent(this.query),
      })
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.GetCommitsResponse;

        if (err) {
          this.pages = [];
          this.commits = [];
          this.message = getErrorMessage(res, err);
          this.isLoading = false;
          this.displayUpdateNotice = false;
        } else {
          this.pages = data.pages.map(c => c + 1);
          this.commits = data.commits;
          this.message = null;
          this.isLoading = false;
          this.displayUpdateNotice = false;
          this.checkUpdate();
        }
      });
  };

  @action
  fetchWelcomePanel = () => {
    WpApi
      .get('display-welcome-panel')
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.DisplayWelcomePanelResponse;

        if (err) {
          return;
        }

        this.displayWelcomePanel = data === true;
      });
  };

  @action
  checkUpdate = () => {
    const { query, commits, isLoading, page } = this;

    if (!commits.length || isLoading) {
      return;
    }

    WpApi
      .get('should-update')
      .query({
        query: encodeURIComponent(query),
        latestCommit: commits[0].hash,
      })
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.ShouldUpdateResponse;

        if (err) {
          this.displayUpdateNotice = false;
          this.isDirtyWorkingDirectory = false;
          clearInterval(this.refreshInterval);
        } else {
          this.displayUpdateNotice = !page && data.update === true;
          this.isDirtyWorkingDirectory = data.cleanWorkingDirectory !== true;
        }
      });
  };

  @action
  undoCommits = (commits: string[]) => {
    this.wpUndoRollback('undo', { commits: commits });
  };

  @action
  rollbackToCommit = (hash: string) => {
    this.wpUndoRollback('rollback', { commit: hash });
  };

  @action
  changeDisplayServicePanel = () => {
    this.displayServicePanel = !this.displayServicePanel;
  };

  @action
  onCommitsSelect = (commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    const { commits } = this;
    let { selectedCommits, lastSelectedCommit } = this;
    const isBulk = commitsToSelect.length > 1;

    commitsToSelect
      .filter(commit => commit.canUndo)
      .forEach(commit => {
        let lastIndex = -1;
        const index = indexOf(commits, commit);

        if (!isBulk && isShiftKey) {
          lastIndex = indexOf(commits, lastSelectedCommit);
        }

        lastIndex = lastIndex === -1 ? index : lastIndex;

        const step = index < lastIndex ? -1 : 1;
        const cond = index + step;
        for (let i = lastIndex; i !== cond; i += step) {
          const currentCommit = commits[i];
          const currentIndex = indexOf(selectedCommits, currentCommit);

          if (isChecked && currentIndex === -1) {
            selectedCommits.push(currentCommit);
          } else if (!isChecked && currentIndex !== -1) {
            selectedCommits.splice(currentIndex, 1);
          }

          lastSelectedCommit = currentCommit;
        }
      });

    this.selectedCommits = selectedCommits;
    this.lastSelectedCommit = isBulk ? null : lastSelectedCommit;
  };

  @action
  clearSelection = () => {
    this.selectedCommits = [];
    this.lastSelectedCommit = null;
  };

  @action
  onCommit = (message: string) => {
    this.updateProgress({ percent: 0 });

    WpApi
      .post('commit')
      .send({ 'commit-message': message })
      .on('progress', this.updateProgress)
      .end(this.wpCommitDiscardEnd('Changes have been committed.'));
  };

  @action
  onDiscard = () => {
    this.updateProgress({ percent: 0 });

    WpApi
      .post('discard-changes')
      .on('progress', this.updateProgress)
      .end(this.wpCommitDiscardEnd('Changes have been discarded.'));
  };

  @action
  onFilterQueryChange = (query: string) => {
    this.query = query;
  };

  @action
  onFilter = () => {
    if (this.page > 0) {
      this.router.transitionTo(routes.home);
    } else {
      this.fetchCommits();
    }
  };

  @action
  onWelcomePanelHide = () => {
    this.displayWelcomePanel = false;

    WpApi
      .post('hide-welcome-panel')
      .end((err: any, res: request.Response) => {
        this.fetchCommits();
      });
  };
}

const appStore = new AppStore();

export default appStore;
