/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='../Commits/Commits.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');
import request = require('superagent');
import CommitsTable = require('../Commits/CommitsTable.react');
import FlashMessage = require('../common/FlashMessage.react');
import ProgressBar = require('../common/ProgressBar.react');
import ServicePanel = require('../ServicePanel/ServicePanel.react');
import ServicePanelButton = require('../ServicePanel/ServicePanelButton.react');
import WelcomePanel = require('../WelcomePanel/WelcomePanel.react');
import revertDialog = require('../Commits/revertDialog');
import moment = require('moment');
import config = require('../config');
import WpApi = require('../services/WpApi');

require('./HomePage.less');

const DOM = React.DOM;
const routes = config.routes;

interface HomePageProps {
  router: ReactRouter.Context;
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
}

class HomePage extends React.Component<HomePageProps, HomePageState> {

  constructor() {
    super();
    this.state = {
      pages: [],
      commits: [],
      message: null,
      loading: true,
      displayServicePanel: false,
      displayWelcomePanel: false
    };

    this.onUndo = this.onUndo.bind(this);
    this.onRollback = this.onRollback.bind(this);
  }

  componentDidMount() {
    this.fetchWelcomePanel();
    this.fetchCommits();
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    this.fetchCommits(nextProps.params);
  }

  fetchCommits(params = this.props.params) {
    this.setState({ loading: true });
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);

    const page = (parseInt(params.page, 10) - 1) || 0;

    if (page === 0) {
      this.props.router.transitionTo(routes.home);
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
            loading: false
          });
        } else {
          this.setState({
            pages: res.body.pages.map(c => c + 1),
            commits: <Commit[]>res.body.commits,
            message: null,
            loading: false
          });
        }
      });
  }

  fetchWelcomePanel() {
    WpApi
      .get('display-welcome-panel')
      .end((err: any, res: request.Response) => {
        if (res.body) {
          this.setState({
            displayWelcomePanel: true
          });
        } else {
          this.setState({
            displayWelcomePanel: false
          });
        }
      });
  }

  undoCommit(hash: string) {
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);
    this.setState({ loading: true });
    WpApi
      .get('undo')
      .query({commit: hash})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: res.body[0],
            loading: false
          });
        } else {
          this.fetchCommits();
        }
      });
  }

  rollbackToCommit(hash: string) {
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);
    this.setState({ loading: true });
    WpApi
      .get('rollback')
      .query({commit: hash})
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: res.body[0],
            loading: false
          });
        } else {
          this.fetchCommits();
        }
      });
  }

  toggleServicePanel() {
    this.setState({
      displayServicePanel: !this.state.displayServicePanel
    });
  }

  sendBugReport(values: Object) {
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);

    WpApi
      .post('submit-bug')
      .send(values)
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: res.body[0]
          });
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

  onUndo(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const message = e.target.getAttribute('data-message');
    const title = DOM.span(null, 'Undo ', DOM.em(null, message), ' ?');

    revertDialog.revertDialog.call(this, title, () => this.undoCommit(hash));
  }

  onRollback(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const date = moment(e.target.getAttribute('data-date')).format('LLL');
    const title = DOM.span(null, 'Roll back to ', DOM.em(null, date), ' ?');

    revertDialog.revertDialog.call(this, title, () => this.rollbackToCommit(hash));
  }

  onWelcomePanelHide(e) {
    e.preventDefault();

    this.setState({
      displayWelcomePanel: false
    });

    WpApi
      .post('hide-welcome-panel')
      .end((err: any, res: request.Response) => {
        this.fetchCommits();
      });
  }

  render() {
    return DOM.div({className: this.state.loading ? 'loading' : ''},
      React.createElement(ProgressBar, {ref: 'progress'}),
      React.createElement(ServicePanelButton, <ServicePanelButton.Props>{
        onClick: this.toggleServicePanel.bind(this)
      }),
      DOM.h1({className: 'vp-header'}, 'VersionPress'),
      this.state.message
        ? React.createElement(FlashMessage, <FlashMessage.Props>this.state.message)
        : null,
      React.createElement(ServicePanel, <ServicePanel.Props>{
        display: this.state.displayServicePanel,
        onSubmit: this.sendBugReport.bind(this)
      }),
      this.state.displayWelcomePanel
        ? React.createElement(WelcomePanel, <WelcomePanel.Props>{ onHide: this.onWelcomePanelHide.bind(this) })
        : null,
      React.createElement(CommitsTable, <CommitsTable.Props>{
        currentPage: parseInt(this.props.params.page, 10) || 1,
        pages: this.state.pages,
        commits: this.state.commits,
        onUndo: this.onUndo,
        onRollback: this.onRollback
      })
    );
  }

}

module HomePage {
  export interface Props extends HomePageProps {}
}

export = HomePage;
