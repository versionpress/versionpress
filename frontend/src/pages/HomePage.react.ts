/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='../Commits/Commits.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');
import routes = require('../routes');
import request = require('superagent');
import CommitsTable = require('../Commits/CommitsTable.react');
import FlashMessage = require('../common/FlashMessage.react');
import ProgressBar = require('../common/ProgressBar.react');
import config = require('../config');

require('./HomePage.less');

const DOM = React.DOM;

interface HomePageProps {
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
}

class HomePage extends React.Component<HomePageProps, HomePageState> {

  constructor() {
    super();
    this.state = {
      pages: [],
      commits: [],
      message: null,
      loading: true
    };
  }

  static contextTypes = {
    router: React.PropTypes.func.isRequired
  };

  componentDidMount() {
    this.fetchData();
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    this.fetchData(nextProps.params);
  }

  fetchData(params = this.props.params) {
    this.setState({ loading: true });
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);

    const page = (parseInt(params.page, 10) - 1) || 0;

    if (page === 0) {
      const router = <ReactRouter.Context> this.context.router;
      router.transitionTo(routes.defaultRoute.props.name);
    }

    request
      .get(config.apiBaseUrl + '/commits')
      .query({page: page})
      .accept('application/json')
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
            pages: res.body.pages,
            commits: <Commit[]>res.body.commits,
            message: null,
            loading: false
          });
        }
      });
  }

  undoCommit(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);
    this.setState({ loading: true });
    request
      .get(config.apiBaseUrl + '/undo')
      .query({commit: hash})
      .set('Accept', 'application/json')
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: res.body[0],
            loading: false
          });
        } else {
          this.fetchData();
        }
      });
  }

  rollbackToCommit(e) {
    e.preventDefault();
    const hash = e.target.getAttribute('data-hash');
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);
    this.setState({ loading: true });
    request
      .get(config.apiBaseUrl + '/rollback')
      .query({commit: hash})
      .set('Accept', 'application/json')
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            message: res.body[0],
            loading: false
          });
        } else {
          this.fetchData();
        }
      });
  }

  render() {
    return DOM.div({className: this.state.loading ? 'loading' : ''},
      React.createElement(ProgressBar, {ref: 'progress'}),
      DOM.h1({className: 'vp-header'}, 'VersionPress'),
      this.state.message
        ? React.createElement(FlashMessage, <FlashMessage.Props>this.state.message)
        : null,
      React.createElement(CommitsTable, <CommitsTable.Props>{
        currentPage: parseInt(this.props.params.page, 10) || 1,
        pages: this.state.pages,
        commits: this.state.commits,
        onUndo: this.undoCommit.bind(this),
        onRollback: this.rollbackToCommit.bind(this)
      })
    );
  }

}

export = HomePage;
