/// <reference path='../common/Commits.d.ts' />
/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
import * as ReactRouter from 'react-router';
import * as request from 'superagent';
import * as moment from 'moment';
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
import { indexOf } from '../../utils/CommitUtils';
import { revertDialog } from '../portal/portal';
import { getErrorMessage, getDiff, getGitStatus, parsePageNumber } from './utils';

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

  private wpCommitDiscardEnd = (successMessage: string) => {
    return (err: any, res: request.Response) => {
      if (err) {
        this.setState({
          message: getErrorMessage(res, err),
        });
      } else {
        this.setState({
          isDirtyWorkingDirectory: false,
          message: {
            code: 'updated',
            message: successMessage,
          },
        });
        this.fetchCommits();
      }
      return !err;
    };
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

    const page = parsePageNumber(params.page);
    if (page < 1) {
      this.context.router.transitionTo(routes.home);
    }

    WpApi
      .get('commits')
      .query({
        page: page,
        query: encodeURIComponent(this.state.query),
      })
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
    const { query, commits, isLoading } = this.state;

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

  onCommitsSelect = (commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    const { commits } = this.state;
    let { selectedCommits, lastSelectedCommit } = this.state;
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

    this.setState({
      selectedCommits: selectedCommits,
      lastSelectedCommit: isBulk ? null : lastSelectedCommit,
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

      revertDialog(title, () => this.undoCommits(hashes));
    }
  };

  onClearSelection = () => {
    this.setState({
      selectedCommits: [],
      lastSelectedCommit: null,
    });
  };

  onCommit = (message: string) => {
    this.updateProgress({ percent: 0 });

    WpApi
      .post('commit')
      .send({ 'commit-message': message })
      .on('progress', this.updateProgress)
      .end(this.wpCommitDiscardEnd('Changes have been committed.'));
  };

  onDiscard = () => {
    this.updateProgress({ percent: 0 });

    WpApi
      .post('discard-changes')
      .on('progress', this.updateProgress)
      .end(this.wpCommitDiscardEnd('Changes have been discarded.'));
  };

  onFilterQueryChange = (query: string) => {
    this.setState({
      query: query,
    });
  };

  onFilter = () => {
    if (parsePageNumber(this.props.params.page) > 0) {
      this.context.router.transitionTo(routes.home);
    } else {
      this.fetchCommits();
    }
  };

  onUndo = (hash: string, message: string) => {
    const title = (
      <span>Undo <em>{message}</em>?</span>
    );

    revertDialog(title, () => this.undoCommits([hash]));
  };

  onRollback = (hash: string, date: string) => {
    const title = (
      <span>Roll back to <em>{moment(date).format('LLL')}</em>?</span>
    );

    revertDialog(title, () => this.rollbackToCommit(hash));
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
            diffProvider={{ getDiff: getDiff }}
            gitStatusProvider={{ getGitStatus: getGitStatus }}
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
            enableActions={!isDirtyWorkingDirectory}
            onBulkAction={this.onBulkAction}
            onClearSelection={this.onClearSelection}
            selectedCommits={selectedCommits}
          />
        </div>
        <CommitsTable
          pages={pages}
          commits={commits}
          selectedCommits={selectedCommits}
          enableActions={!isDirtyWorkingDirectory}
          diffProvider={{ getDiff: getDiff }}
          onUndo={this.onUndo}
          onRollback={this.onRollback}
          onCommitsSelect={this.onCommitsSelect}
        />
      </div>
    );
  }

}
