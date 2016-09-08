/// <reference path='../common/Commits.d.ts' />
/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
import * as ReactRouter from 'react-router';
import * as request from 'superagent';
import * as moment from 'moment';
import * as Promise from 'core-js/es6/promise';
import * as classNames from 'classnames';

import BulkActionPanel from '../bulk-action-panel/BulkActionPanel';
import CommitPanel from '../commit-panel/CommitPanel';
import CommitsTable from '../commits-table/CommitsTable';
import Filter from '../filter/Filter';
import FlashMessage from '../common/flash-message/FlashMessage';
import ProgressBar from '../common/progress-bar/ProgressBar';
import ServicePanel from '../service-panel/ServicePanel';
import UpdateNotice from './update-notice/UpdateNotice';
import VpTitle from './vp-title/VpTitle';
import WelcomePanel from '../welcome-panel/WelcomePanel';
import config from '../../config/config';
import * as WpApi from '../../services/WpApi';
import { getPage, getErrorMessage } from './utils';
import { indexOf } from '../../utils/CommitUtils';
import { revertDialog } from '../portal/portal';

import './HomePage.less';

const routes = config.routes;

interface HomePageProps {
  params: {
    page?: string,
  };
}

interface HomePageState {
  pages?: number[];
  query?: string;
  commits?: Commit[];
  selectedCommits?: Commit[];
  lastSelectedCommit?: Commit;
  message?: InfoMessage;
  isLoading?: boolean;
  displayServicePanel?: boolean;
  displayWelcomePanel?: boolean;
  displayUpdateNotice?: boolean;
  isDirtyWorkingDirectory?: boolean;
  progress?: number;
}

interface HomePageContext {
  router: ReactRouter.Context;
}

export default class HomePage extends React.Component<HomePageProps, HomePageState> {

  static contextTypes: React.ValidationMap<any> = {
    router: React.PropTypes.func.isRequired,
  };

  context: HomePageContext;

  state = {
    pages: [],
    query: '',
    commits: [],
    selectedCommits: [],
    lastSelectedCommit: null,
    message: null,
    isLoading: true,
    displayServicePanel: false,
    displayWelcomePanel: false,
    displayUpdateNotice: false,
    isDirtyWorkingDirectory: false,
    progress: 100,
  };

  private refreshInterval;

  private updateProgress = (e: {percent: number}) => {
    this.setState({
      progress: e.percent,
    });
  };

  private setLoading = () => {
    this.setState({
      isLoading: true,
      progress: 0,
    });
  };

  private wpUndoRollback = (name: string, query: any) => {
    this.setLoading();

    WpApi
      .get(name)
      .query(query)
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: getErrorMessage(res, err),
            isLoading: false,
          });
        } else {
          this.context.router.transitionTo(routes.home);
          document.location.reload();
        }
      });
  };

  componentDidMount() {
    this.fetchWelcomePanel();
    this.fetchCommits();
    this.refreshInterval = setInterval(() => this.checkUpdate(), 10 * 1000);
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    this.fetchCommits(nextProps.params);
  }

  componentWillUnmount() {
    clearInterval(this.refreshInterval);
  }

  fetchCommits = (params = this.props.params) => {
    this.setLoading();

    const page = getPage(params.page);
    if (page < 1) {
      this.context.router.transitionTo(routes.home);
    }

    WpApi
      .get('commits')
      .query({page: page, query: encodeURIComponent(this.state.query)})
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.GetCommitsResponse;

        if (err) {
          this.setState({
            pages: [],
            commits: [],
            message: getErrorMessage(res, err),
            isLoading: false,
            displayUpdateNotice: false,
          });
        } else {
          this.setState({
            pages: data.pages.map(c => c + 1),
            commits: data.commits,
            message: null,
            isLoading: false,
            displayUpdateNotice: false,
          });
        }
      });
  };

  fetchWelcomePanel = () => {
    WpApi
      .get('display-welcome-panel')
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.DisplayWelcomePanelResponse;
        if (err) {
          return;
        }

        this.setState({
          displayWelcomePanel: data === true,
        });
      });
  };

  checkUpdate = () => {
    const {
      query,
      commits,
      isLoading,
    } = this.state;

    if (!commits.length || isLoading) {
      return;
    }

    WpApi
      .get('should-update')
      .query({query: encodeURIComponent(query), latestCommit: commits[0].hash})
      .end((err: any, res: request.Response) => {
        const data = res.body.data as VpApi.ShouldUpdateResponse;
        if (err) {
          this.setState({
            displayUpdateNotice: false,
            isDirtyWorkingDirectory: false,
          });
          clearInterval(this.refreshInterval);
        } else {
          this.setState({
            displayUpdateNotice: !this.props.params.page && data.update === true,
            isDirtyWorkingDirectory: data.cleanWorkingDirectory !== true,
          });
        }
      });
  };

  undoCommits = (commits: string[]) => {
    this.wpUndoRollback('undo', { commits: commits });
  };

  rollbackToCommit = (hash: string) => {
    this.wpUndoRollback('rollback', { commit: hash });
  };

  onServicePanelClick = () => {
    this.setState({
      displayServicePanel: !this.state.displayServicePanel,
    });
  };

  onCommitsSelect = (commits: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    let { selectedCommits, lastSelectedCommit } = this.state;
    const bulk = commits.length > 1;

    commits
      .filter((commit: Commit) => commit.canUndo)
      .forEach((commit: Commit) => {
        let lastIndex = -1;
        const index = indexOf(this.state.commits, commit);

        if (!bulk && isShiftKey) {
          const last = this.state.lastSelectedCommit;
          lastIndex = indexOf(this.state.commits, last);
        }

        if (lastIndex === -1) {
          lastIndex = index;
        }

        const step = (index < lastIndex ? -1 : 1);
        const cond = index + step;
        for (let i = lastIndex; i !== cond; i += step) {
          const currentCommit = this.state.commits[i];
          const index = indexOf(selectedCommits, currentCommit);
          if (isChecked && index === -1) {
            selectedCommits.push(currentCommit);
          } else if (!isChecked && index !== -1) {
            selectedCommits.splice(index, 1);
          }
          lastSelectedCommit = currentCommit;
        }
      });

    this.setState({
      selectedCommits: selectedCommits,
      lastSelectedCommit: (bulk ? null : lastSelectedCommit),
    });
  };

  onBulkAction = (action: string) => {
    if (action === 'undo') {
      const { selectedCommits } = this.state;
      const count = selectedCommits.length;

      const title = (
        <span>Undo <em>{count} {count === 1 ? 'change' : 'changes'}</em>?</span>
      );
      const hashes = selectedCommits.map((commit: Commit) => commit.hash);

      revertDialog.call(this, title, () => this.undoCommits(hashes));
    }
  };

  onClearSelection = () => {
    this.setState({
      selectedCommits: [],
      lastSelectedCommit: null,
    });
  };

  onCommit = (message: string) => {
    this.setState({
      progress: 0,
    });

    const values = { 'commit-message': message };

    WpApi
      .post('commit')
      .send(values)
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: getErrorMessage(res, err),
          });
        } else {
          this.setState({
            isDirtyWorkingDirectory: false,
            message: {
              code: 'updated',
              message: 'Changes have been committed.',
            },
          });
          this.fetchCommits();
        }
        return !err;
      });
  };

  onDiscard = () => {
    this.setState({
      progress: 0,
    });

    WpApi
      .post('discard-changes')
      .on('progress', this.updateProgress)
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: getErrorMessage(res, err),
          });
        } else {
          this.setState({
            isDirtyWorkingDirectory: false,
            message: {
              code: 'updated',
              message: 'Changes have been discarded.',
            },
          });
        }
        return !err;
      });
  };

  onFilterQueryChange = (query: string) => {
    this.setState({
      query: query,
    });
  };

  onFilter = () => {
    if (getPage(this.props.params.page) > 0) {
      this.context.router.transitionTo(routes.home);
    } else {
      this.fetchCommits();
    }
  };

  onUndo = (hash: string, message: string) => {
    const title = (
      <span>Undo <em>{message}</em>?</span>
    );

    revertDialog.call(this, title, () => this.undoCommits([hash]));
  };

  onRollback = (hash: string, date: string) => {
    const title = (
      <span>Roll back to <em>{moment(date).format('LLL')}</em>?</span>
    );

    revertDialog.call(this, title, () => this.rollbackToCommit(hash));
  };

  onWelcomePanelHide = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      displayWelcomePanel: false,
    });

    WpApi
      .post('hide-welcome-panel')
      .end((err: any, res: request.Response) => {
        this.fetchCommits();
      });
  };

  onUpdateNoticeClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.fetchCommits();
  };

  getGitStatus = () => {
    return new Promise(function(resolve, reject) {
      WpApi
        .get('git-status')
        .end((err, res: request.Response) => {
          const data = res.body.data as VpApi.GetGitStatusResponse;
          if (err) {
            reject(getErrorMessage(res, err));
          } else {
            resolve(data);
          }
        });
    });
  };

  getDiff = (hash: string) => {
    const query = hash === '' ? null : {commit: hash};

    return new Promise(function(resolve, reject) {
      WpApi
        .get('diff')
        .query(query)
        .end((err, res: request.Response) => {
          const data = res.body.data as VpApi.GetDiffResponse;
          if (err) {
            reject(getErrorMessage(res, err));
          } else {
            resolve(data.diff);
          }
        });
    });
  };

  render() {
    const {
      pages,
      query,
      commits,
      selectedCommits,
      message,
      isLoading,
      displayServicePanel,
      displayWelcomePanel,
      displayUpdateNotice,
      isDirtyWorkingDirectory,
      progress,
    } = this.state;
    const enableActions = !isDirtyWorkingDirectory;

    const homePageClassName = classNames({
      'loading': isLoading,
    });

    return (
      <div className={homePageClassName}>
        <ProgressBar progress={progress} />
        <ServicePanel
          isVisible={displayServicePanel}
          onButtonClick={this.onServicePanelClick}
        >
          <VpTitle />
          {message &&
            <FlashMessage message={message} />
          }
        </ServicePanel>
        {isDirtyWorkingDirectory &&
          <CommitPanel
            diffProvider={{ getDiff: this.getDiff }}
            gitStatusProvider={{ getGitStatus: this.getGitStatus }}
            onCommit={this.onCommit}
            onDiscard={this.onDiscard}
          />
        }
        {displayWelcomePanel &&
          <WelcomePanel onHide={this.onWelcomePanelHide} />
        }
        {displayUpdateNotice &&
          <UpdateNotice onClick={this.onUpdateNoticeClick} />
        }
        <div className='tablenav top'>
          <Filter
            query={query}
            onQueryChange={this.onFilterQueryChange}
            onFilter={this.onFilter}
          />
          <BulkActionPanel
            enableActions={enableActions}
            onBulkAction={this.onBulkAction}
            onClearSelection={this.onClearSelection}
            selectedCommits={selectedCommits}
          />
        </div>
        <CommitsTable
          pages={pages}
          commits={commits}
          selectedCommits={selectedCommits}
          enableActions={enableActions}
          diffProvider={{ getDiff: this.getDiff }}
          onUndo={this.onUndo}
          onRollback={this.onRollback}
          onCommitsSelect={this.onCommitsSelect}
        />
      </div>
    );
  }

}
