/// <reference path='../components/common/Commits.d.ts' />
/// <reference path='../interfaces/State.d.ts' />

import { action, observable, runInAction } from 'mobx';
import { appHistory } from '../routes';
import * as request from 'superagent';

import config from '../config/config';
import CommitRow from './CommitRow';
import * as WpApi from '../services/WpApi';
import { indexOf } from '../utils/CommitUtils';
import { getErrorMessage, parsePageNumber } from './utils';

import commitsTableStore from './commitsTableStore';
import navigationStore from './navigationStore';
import servicePanelStore from './servicePanelStore';

const routes = config.routes;

class AppStore {
  @observable page: number = 0;
  @observable selectedCommits: Commit[] = [];
  @observable lastSelectedCommit: Commit = null;
  @observable displayWelcomePanel: boolean = false;
  @observable displayUpdateNotice: boolean = false;
  @observable isDirtyWorkingDirectory: boolean = false;
  @observable progress: number = 100;

  private refreshInterval;

  constructor() {
    this.refreshInterval = setInterval(this.checkUpdate, 10 * 1000);
  }

  @action
  private setLoading = (isLoading: boolean) => {
    commitsTableStore.isLoading = isLoading;
    this.progress = isLoading ? 0 : 100;
  };

  @action
  private wpUndoRollback = (name: string, query: any) => {
    this.setLoading(true);

    WpApi
      .get(name)
      .query(query)
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        if (err) {
          runInAction(() => {
            this.setLoading(false);
            servicePanelStore.changeMessage(getErrorMessage(res, err));
          });
        } else {
          appHistory.push(routes.home);
          document.location.reload();
        }
      });
  };

  @action
  init = (page: number | string = 0) => {
    this.page = parsePageNumber(page);
    this.fetchWelcomePanel();
    this.fetchCommits();
  };

  @action
  updateProgress = (progress: ProgressEvent | number) => {
    if (typeof progress === 'number') {
      this.progress = progress;
    } else if (progress.total > 0) {
      this.progress = progress.loaded / progress.total * 100;
    }
  };

  @action
  fetchCommits = (page: number | string = this.page) => {
    this.setLoading(true);

    if (typeof page === 'string' && parsePageNumber(page) < 1) {
      appHistory.replace(routes.home);
    }

    this.page = parsePageNumber(page);

    WpApi
      .get('commits')
      .query({
        page: this.page,
        query: encodeURIComponent(navigationStore.query),
      })
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.GetCommitsResponse;

        runInAction(() => {
          this.setLoading(false);
          this.displayUpdateNotice = false;

          if (err) {
            commitsTableStore.changePages([]);
            commitsTableStore.changeCommitRows([]);
            servicePanelStore.changeMessage(getErrorMessage(res, err));
          } else {
            commitsTableStore.changePages(data.pages.map(c => c + 1));
            commitsTableStore.changeCommitRows(data.commits.map(commit => (
              new CommitRow(commit, indexOf(this.selectedCommits, commit) !== -1))
            ));
            servicePanelStore.changeMessage(null);

            this.checkUpdate();
          }
        });
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
    const { commits, isLoading } = commitsTableStore;

    if (!commits.length || isLoading) {
      return;
    }

    WpApi
      .get('should-update')
      .query({
        query: encodeURIComponent(navigationStore.query),
        latestCommit: commits[0].hash,
      })
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.ShouldUpdateResponse;

        runInAction(() => {
          if (err) {
            this.displayUpdateNotice = false;
            this.isDirtyWorkingDirectory = false;
            clearInterval(this.refreshInterval);
          } else {
            this.displayUpdateNotice = !this.page && data.update === true;
            this.isDirtyWorkingDirectory = data.cleanWorkingDirectory !== true;
          }
        });
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
  selectCommits = (commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    const { commits } = commitsTableStore;
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
    commitsTableStore.updateSelectedCommits(this.selectedCommits);
  };

  @action
  clearSelection = () => {
    this.selectedCommits = [];
    this.lastSelectedCommit = null;
    commitsTableStore.deselectAllCommits();
  };

  @action
  filter = () => {
    if (this.page > 0) {
      this.page = 0;
      appHistory.push(routes.home);
    }
    this.fetchCommits();
  };

  @action
  hideWelcomePanel = () => {
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
