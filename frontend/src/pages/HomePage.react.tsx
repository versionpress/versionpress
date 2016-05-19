/// <reference path='../../typings/typings.d.ts' />
/// <reference path='../Commits/Commits.d.ts' />

import * as React from 'react';
import * as ReactRouter from 'react-router';
import * as request from 'superagent';
import * as moment from 'moment';
import * as Promise from 'core-js/es6/promise';
import update = require('react-addons-update');
import BulkActionPanel from '../BulkActionPanel/BulkActionPanel.react';
import CommitPanel from '../CommitPanel/CommitPanel.react';
import CommitsTable from '../Commits/CommitsTable.react';
import Filter from '../Filter/Filter.react';
import FlashMessage from '../common/FlashMessage.react';
import ProgressBar from '../common/ProgressBar.react';
import ServicePanel from '../ServicePanel/ServicePanel.react';
import ServicePanelButton from '../ServicePanel/ServicePanelButton.react';
import WelcomePanel from '../WelcomePanel/WelcomePanel.react';
import * as revertDialog from '../Commits/revertDialog';
import * as WpApi from '../services/WpApi';
import {indexOf} from '../Commits/CommitUtils';
import config from '../config';

import './HomePage.less';

const routes = config.routes;

interface HomePageProps extends React.Props<JSX.Element> {
  params: {
    page?: string
  };
}

interface HomePageState {
  pages?: number[];
  query?: string;
  commits?: Commit[];
  selected?: Commit[];
  lastSelected?: Commit;
  message?: {
    code: string,
    message: string,
    details?: string
  };
  loading?: boolean;
  displayServicePanel?: boolean;
  displayWelcomePanel?: boolean;
  displayUpdateNotice?: boolean;
  dirtyWorkingDirectory?: boolean;
}

export default class HomePage extends React.Component<HomePageProps, HomePageState> {

  static contextTypes: React.ValidationMap<any> = {
    router: React.PropTypes.func.isRequired
  };

  private refreshInterval;

  constructor() {
    super();
    this.state = {
      pages: [],
      query: '',
      commits: [],
      selected: [],
      lastSelected: null,
      message: null,
      loading: true,
      displayServicePanel: false,
      displayWelcomePanel: false,
      displayUpdateNotice: false,
      dirtyWorkingDirectory: false
    };

    this.onFilter = this.onFilter.bind(this);
    this.onUndo = this.onUndo.bind(this);
    this.onRollback = this.onRollback.bind(this);
    this.onCommitSelect = this.onCommitSelect.bind(this);
    this.checkUpdate = this.checkUpdate.bind(this);
  }

  static getErrorMessage(res: request.Response, err: any) {
    if (res) {
      const body = Array.isArray(res.body) ? res.body[0] : res.body;
      if ('code' in body && 'message' in body) {
        return body;
      }
    }
    console.error(err);
    return {
      code: 'error',
      message: 'VersionPress is not able to connect to WordPress site. Please try refreshing the page.',
      details: err
    };
  }

  componentDidMount() {
    this.fetchWelcomePanel();
    this.fetchCommits();
    this.refreshInterval = setInterval(() => this.checkUpdate(), 10 * 1000);
  }
  componentWillUnmount() {
    clearInterval(this.refreshInterval);
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    this.fetchCommits(nextProps.params);
  }

  fetchCommits(params = this.props.params) {
    const router: ReactRouter.Context = (this.context as any).router;
    this.setState({ loading: true });
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);

    const page = (parseInt(params.page, 10) - 1) || 0;

    if (page < 1) {
      router.transitionTo(routes.home);
    }

    WpApi
      .get('commits')
      .query({page: page, query: encodeURIComponent(this.state.query)})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            pages: [],
            commits: [],
            message: HomePage.getErrorMessage(res, err),
            loading: false,
            displayUpdateNotice: false
          });
        } else {
          this.setState({
            pages: res.body.pages.map(c => c + 1),
            commits: res.body.commits as Commit[],
            message: null,
            loading: false,
            displayUpdateNotice: false
          });
          this.checkUpdate();
        }
      });
  }

  fetchWelcomePanel() {
    WpApi
      .get('display-welcome-panel')
      .end((err: any, res: request.Response) => {
        if (err) {
          return;
        }
        const body = Array.isArray(res.body) ? res.body[0] : res.body;
        if (body === true) {
          this.setState({displayWelcomePanel: true});
        } else {
          this.setState({displayWelcomePanel: false});
        }
      });
  }

  checkUpdate() {
    if (!this.state.commits.length || this.state.loading) {
      return;
    }
    WpApi
      .get('should-update')
      .query({query: encodeURIComponent(this.state.query), latestCommit: this.state.commits[0].hash})
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            displayUpdateNotice: false,
            dirtyWorkingDirectory: false
          });
          clearInterval(this.refreshInterval);
        } else {
          this.setState({
            displayUpdateNotice: !this.props.params.page && res.body.update === true,
            dirtyWorkingDirectory: res.body.cleanWorkingDirectory !== true
          });
        }
      });
  }

  undoCommits(commits: string[]) {
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);
    this.setState({ loading: true });
    WpApi
      .get('undo')
      .query({commits: commits})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({message: HomePage.getErrorMessage(res, err), loading: false});
        } else {
          const router:ReactRouter.Context = (this.context as any).router;
          router.transitionTo(routes.home);
          document.location.reload();
        }
      });
  }

  rollbackToCommit(hash: string) {
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);
    this.setState({ loading: true });
    WpApi
      .get('rollback')
      .query({commit: hash})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({message: HomePage.getErrorMessage(res, err), loading: false});
        } else {
          const router:ReactRouter.Context = (this.context as any).router;
          router.transitionTo(routes.home);
          document.location.reload();
        }
      });
  }

  getGitStatus() {
    return new Promise(function(resolve, reject) {
      WpApi
        .get('git-status')
        .end((err, res: request.Response) => {
          if (err) {
            reject(HomePage.getErrorMessage(res, err));
          } else {
            resolve(res.body);
          }
        });
    });
  }

  getDiff(hash: string) {
    const query = hash === '' ? null : {commit: hash};
    return new Promise(function(resolve, reject) {
      WpApi
        .get('diff')
        .query(query)
        .end((err, res: request.Response) => {
          if (err) {
            reject(HomePage.getErrorMessage(res, err));
          } else {
            resolve(res.body.diff);
          }
        });
    });
  }

  toggleServicePanel() {
    this.setState({displayServicePanel: !this.state.displayServicePanel});
  }

  onCommitSelect(commits: Commit[], check: boolean, shiftKey: boolean) {
    let selected = this.state.selected,
        lastSelected = this.state.lastSelected;
    const bulk = commits.length > 1;

    commits
      .filter((commit: Commit) => commit.canUndo)
      .forEach((commit: Commit) => {
        let lastIndex = -1;
        const index = indexOf(this.state.commits, commit);

        if (!bulk && shiftKey) {
          const last = this.state.lastSelected;
          lastIndex = indexOf(this.state.commits, last);
        }

        if (lastIndex === -1) {
          lastIndex = index;
        }

        const step = (index < lastIndex ? -1 : 1);
        const cond = index + step;
        for (let i = lastIndex; i != cond; i += step) {
          const current = this.state.commits[i];
          const index = indexOf(selected, current);
          if (check && index === -1) {
            selected = update(selected, {$push: [current]});
          } else if (!check && index !== -1) {
            selected = update(selected, {$splice: [[index, 1]]});
          }
          lastSelected = current;
        }
      });

    this.setState({
      selected: selected,
      lastSelected: (bulk ? null : lastSelected)
    });
  }

  onBulkAction(action: string) {
    if (action === 'undo') {
      const title = (
        <span>Undo <em>{this.state.selected.length} {this.state.selected.length === 1 ? 'change' : 'changes'}</em>?</span>
      );
      const hashes = this.state.selected.map((commit: Commit) => commit.hash);

      revertDialog.revertDialog.call(this, title, () => this.undoCommits(hashes));
    }
  }

  onClearSelection() {
    this.setState({
      selected: [],
      lastSelected: null
    });
  }

  onCommit(message: string) {
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);

    const values = { 'commit-message': message };

    WpApi
      .post('commit')
      .send(values)
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({message: HomePage.getErrorMessage(res, err)});
        } else {
          this.setState({
            dirtyWorkingDirectory: false,
            message: {
              code: 'updated',
              message: 'Changes have been committed.'
            }
          });
          this.fetchCommits();
        }
        return !err;
      });
  }

  onDiscard() {
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);

    WpApi
      .post('discard-changes')
      .on('progress', (e: {percent: number}) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({message: HomePage.getErrorMessage(res, err)});
        } else {
          this.setState({
            dirtyWorkingDirectory: false,
            message: {
              code: 'updated',
              message: 'Changes have been discarded.'
            }
          });
        }
        return !err;
      });
  }

  onFilter(query: string) {
    this.setState({
      query: query
    }, () => {
      const page = (parseInt(this.props.params.page, 10) - 1) || 0;
      if (page > 0) {
        const router:ReactRouter.Context = (this.context as any).router;
        router.transitionTo(routes.home);
      } else {
        this.fetchCommits();
      }
    });
  }

  onUndo(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const message = e.target.getAttribute('data-message');
    const title = (
      <span>Undo <em>{message}</em>?</span>
    );

    revertDialog.revertDialog.call(this, title, () => this.undoCommits([hash]));
  }

  onRollback(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const date = moment(e.target.getAttribute('data-date')).format('LLL');
    const title = (
      <span>Roll back to <em>{date}</em>?</span>
    );

    revertDialog.revertDialog.call(this, title, () => this.rollbackToCommit(hash));
  }

  onWelcomePanelHide(e) {
    e.preventDefault();

    this.setState({displayWelcomePanel: false});

    WpApi
      .post('hide-welcome-panel')
      .end((err: any, res: request.Response) => {
        this.fetchCommits();
      });
  }

  render() {
    const enableActions = !this.state.dirtyWorkingDirectory;

    return (
      <div className={this.state.loading ? 'loading' : ''}>
        <ProgressBar ref='progress' />
        <ServicePanelButton
          onClick={this.toggleServicePanel.bind(this)}
        />
        <h1 className='vp-header'>VersionPress</h1>
        {this.state.message
          ? <FlashMessage {...this.state.message} />
          : null
        }
        <ServicePanel
          display={this.state.displayServicePanel}
        />
        {this.state.dirtyWorkingDirectory
          ? <CommitPanel
              diffProvider={{ getDiff: this.getDiff }}
              gitStatusProvider={{ getGitStatus: this.getGitStatus }}
              onCommit={this.onCommit.bind(this)}
              onDiscard={this.onDiscard.bind(this)}
            />
          : null
        }
        {this.state.displayWelcomePanel
          ? <WelcomePanel onHide={this.onWelcomePanelHide.bind(this)} />
          : null
        }
        {this.state.displayUpdateNotice
          ? <div className='updateNotice'>
              <span>There are newer changes available.</span>
              <a
                href='#'
                onClick={(e) => { e.preventDefault(); this.fetchCommits(); }}
              >Refresh now.</a>
            </div>
          : null
        }
        <div className='tablenav top'>
          <Filter
            onSubmit={this.onFilter}
          />
          <BulkActionPanel
            enableActions={enableActions}
            onBulkAction={this.onBulkAction.bind(this)}
            onClearSelection={this.onClearSelection.bind(this)}
            selected={this.state.selected}
          />
        </div>
        <CommitsTable
          currentPage={parseInt(this.props.params.page, 10) || 1}
          pages={this.state.pages}
          commits={this.state.commits}
          selected={this.state.selected}
          enableActions={enableActions}
          onCommitSelect={this.onCommitSelect}
          onUndo={this.onUndo}
          onRollback={this.onRollback}
          diffProvider={{ getDiff: this.getDiff }}
        />
      </div>
    );
  }

}
