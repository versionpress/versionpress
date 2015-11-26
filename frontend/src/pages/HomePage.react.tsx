/// <reference path='../../typings/typings.d.ts' />
/// <reference path='../Commits/Commits.d.ts' />

import * as React from 'react';
import * as ReactRouter from 'react-router';
import * as request from 'superagent';
import * as moment from 'moment';
import * as Promise from 'core-js/es6/promise';
import CommitPanel from '../CommitPanel/CommitPanel.react';
import CommitsTable from '../Commits/CommitsTable.react';
import FlashMessage from '../common/FlashMessage.react';
import ProgressBar from '../common/ProgressBar.react';
import ServicePanel from '../ServicePanel/ServicePanel.react';
import ServicePanelButton from '../ServicePanel/ServicePanelButton.react';
import WelcomePanel from '../WelcomePanel/WelcomePanel.react';
import * as revertDialog from '../Commits/revertDialog';
import * as WpApi from '../services/WpApi';
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
  commits?: Commit[];
  message?: {
    code: string,
    message: string
  };
  loading?: boolean;
  displayServicePanel?: boolean;
  displayWelcomePanel?: boolean;
  displayUpdateNotice?: boolean;
  displayCommitPanel?: boolean;
}

export default class HomePage extends React.Component<HomePageProps, HomePageState> {

  private refreshInterval;

  constructor() {
    super();
    this.state = {
      pages: [],
      commits: [],
      message: null,
      loading: true,
      displayServicePanel: false,
      displayWelcomePanel: false,
      displayUpdateNotice: false,
      displayCommitPanel: false
    };

    this.onUndo = this.onUndo.bind(this);
    this.onRollback = this.onRollback.bind(this);
    this.checkUpdate = this.checkUpdate.bind(this);
  }

  static contextTypes: React.ValidationMap<any> = {
    router: React.PropTypes.func.isRequired
  };

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

    if (page === 0) {
      router.transitionTo(routes.home);
    }

    WpApi
      .get('commits')
      .query({page: page})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            commits: [],
            message: res.body[0],
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
        if (res.body === true) {
          this.setState({displayWelcomePanel: true});
        } else {
          this.setState({displayWelcomePanel: false});
        }
      });
  }

  checkUpdate() {
    if (!this.state.commits.length) {
      return;
    }
    WpApi
      .get('should-update')
      .query({latestCommit: this.state.commits[0].hash})
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            displayUpdateNotice: false,
            displayCommitPanel: false
          });
          clearInterval(this.refreshInterval);
        } else {
          this.setState({
            displayUpdateNotice: !this.props.params.page && res.body.update === true,
            displayCommitPanel: res.body.cleanWorkingDirectory !== true
          });
        }
      });
  }

  undoCommit(hash: string) {
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);
    this.setState({ loading: true });
    WpApi
      .get('undo')
      .query({commit: hash})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({message: res.body[0], loading: false});
        } else {
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
          this.setState({message: res.body[0], loading: false});
        } else {
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
            reject(res.body[0]);
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
            reject(res.body[0]);
          } else {
            resolve(res.body.diff);
          }
        });
    });
  }

  toggleServicePanel() {
    this.setState({displayServicePanel: !this.state.displayServicePanel});
  }

  sendBugReport(values: Object) {
    const progressBar = this.refs['progress'] as ProgressBar;
    progressBar.progress(0);

    WpApi
      .post('submit-bug')
      .send(values)
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({message: res.body[0]});
        } else {
          this.setState({
            displayServicePanel: false,
            message: {
              code: 'updated',
              message: 'Bug report was sent. Thank you.'
            }
          });
        }
        return !err;
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
          this.setState({message: res.body[0]});
        } else {
          this.setState({
            displayCommitPanel: false,
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
          this.setState({message: res.body[0]});
        } else {
          this.setState({
            displayCommitPanel: false,
            message: {
              code: 'updated',
              message: 'Changes have been discarded.'
            }
          });
        }
        return !err;
      });
  }

  onUndo(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const message = e.target.getAttribute('data-message');
    const title = (
      <span>Undo <em>{message}</em>?</span>
    );

    revertDialog.revertDialog.call(this, title, () => this.undoCommit(hash));
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
          onSubmit={this.sendBugReport.bind(this)}
        />
        {this.state.displayCommitPanel
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
        <CommitsTable
          currentPage={parseInt(this.props.params.page, 10) || 1}
          pages={this.state.pages}
          commits={this.state.commits}
          onUndo={this.onUndo}
          onRollback={this.onRollback}
          diffProvider={{ getDiff: this.getDiff }}
        />
      </div>
    );
  }

}
