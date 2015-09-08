/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

import CommitPanelCommit = require('./CommitPanelCommit.react');
import CommitPanelNotice = require('./CommitPanelNotice.react');
import CommitPanelDetails = require('./CommitPanelDetails.react');
import CommitPanelOverview = require('./CommitPanelOverview.react');

require('./CommitPanel.less');

const DOM = React.DOM;

interface CommitPanelProps {
  diffProvider: {getDiff: (hash: string) => Promise<string>};
  gitStatusProvider: {getGitStatus: () => Promise<string[][]>};
  onCommit: (message: string) => any;
  onDiscard: () => any;
}

interface CommitPanelState {
  detailsLevel?: string;
  diff?: string;
  gitStatus?: string[][];
  error?: string;
  loading?: boolean;
}

class CommitPanel extends React.Component<CommitPanelProps, CommitPanelState> {

  constructor() {
    super();
    this.state = {detailsLevel: 'none', loading: false};
  }

  render() {
    const className = 'CommitPanel-notice' + (this.state.detailsLevel !== 'none' ? ' CommitPanel-notice--expanded' : '');

    return DOM.div({className: 'CommitPanel'},
      DOM.div({className: className},
        React.createElement(CommitPanelNotice, <CommitPanelNotice.Props>{
          onDetailsLevelChanged: detailsLevel => this.changeDetailsLevel(detailsLevel),
          detailsLevel: this.state.detailsLevel
        }),
        React.createElement(CommitPanelCommit, <CommitPanelCommit.Props>{
          onCommit: this.props.onCommit,
          onDiscard: this.props.onDiscard
        })
      ),
      this.state.error
        ? this.renderError()
        : this.renderDetails()
    );
  }

  private renderError() {
    return DOM.div({className: 'CommitPanel-error'},
      DOM.p(null, this.state.error)
    );
  }

  private renderDetails() {
    if (this.state.detailsLevel === 'none') {
      return null;
    }

    const className = 'CommitPanel-details' + (this.state.loading ? ' loading' : '');

    return DOM.div({className: className},
      this.state.loading ? DOM.div({className: 'CommitPanel-details-loader'}, null) : null,
      this.state.detailsLevel === 'overview'
        ? React.createElement(CommitPanelOverview, <CommitPanelOverview.Props>{gitStatus: this.state.gitStatus})
        : React.createElement(CommitPanelDetails, <CommitPanelDetails.Props>{diff: this.state.diff})
    );
  }

  private changeDetailsLevel(detailsLevel: string) {
    if (detailsLevel === 'overview' && !this.state.gitStatus) {
      this.setState({loading: true});
      this.props.gitStatusProvider.getGitStatus()
        .then(gitStatus => this.setState({
            detailsLevel: detailsLevel,
            gitStatus: gitStatus,
            error: null,
            loading: false
          })
        ).catch(err => {
          this.setState({detailsLevel: detailsLevel, error: err.message, loading: false});
        })
    } else if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.setState({loading: true});
      this.props.diffProvider.getDiff('')
        .then(diff => this.setState({
            detailsLevel: detailsLevel,
            diff: diff,
            error: null,
            loading: false
          })
        ).catch(err => {
          this.setState({detailsLevel: detailsLevel, error: err.message, loading: false});
        });
    } else {
      this.setState({detailsLevel: detailsLevel, error: null, loading: false});
    }
  }

}

module CommitPanel {
  export interface Props extends CommitPanelProps {}
}

export = CommitPanel;
