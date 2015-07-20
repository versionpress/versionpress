/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='../Commits/Commits.d.ts' />

import React = require('react');
import request = require('superagent');
import CommitsTable = require('../Commits/CommitsTable.react');
import ProgressBar = require('../common/ProgressBar.react');
import config = require('../config');

const DOM = React.DOM;

interface HomePageState {
  commits: Commit[];
  loading?: boolean;
}

class HomePage extends React.Component<any, HomePageState> {

  constructor() {
    super();
    this.state = {
      commits: []
      loading: true
    };
  }

  componentDidMount() {
    this.setState({ loading: true });
    const progressBar = <ProgressBar> this.refs['progress'];
    progressBar.progress(0);
    request
      .get(config.apiBaseUrl + '/commits')
      .accept('application/json')
      .on('progress', (e) => progressBar.progress(e.percent))
      .end((err: any, res: request.Response) => {
        if (err) {
          this.setState({
            commits: [],
            loading: false
          });
        } else {
          this.setState({
            commits: <Commit[]>res.body.commits,
            loading: false
          });
        }
      });
  }

  render() {
    return DOM.div({className: this.state.loading ? 'loading' : ''},
      React.createElement(ProgressBar, {ref: 'progress'}),
      DOM.h1({className: 'vp-header'}, 'VersionPress'),
      React.createElement(CommitsTable, <CommitsTable.Props>{
        commits: this.state.commits
      })
    );
  }

}

export = HomePage;
