/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import CommitsTableRowSummary = require('./CommitsTableRowSummary.react');
import CommitsTableRowDetails = require('./CommitsTableRowDetails.react');

const DOM = React.DOM;

interface CommitsTableRowProps {
  commit: Commit;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  diffProvider: {getDiff: (hash: string) => Promise<string>};
}

interface CommitsTableRowState {
  detailsLevel?: string;
  diff?: string;
  error?: string;
}

class CommitsTableRow extends React.Component<CommitsTableRowProps, CommitsTableRowState> {

  constructor() {
    super();
    this.state = {detailsLevel: 'none'};
  }

  render() {
    return DOM.tbody(null,
      React.createElement(CommitsTableRowSummary, <CommitsTableRowSummary.Props>{
        commit: this.props.commit,
        onUndo: this.props.onUndo,
        onRollback: this.props.onRollback,
        onDetailsLevelChanged: detailsLevel => this.changeDetailsLevel(detailsLevel),
        detailsLevel: this.state.detailsLevel
      }),
      this.state.error ? this.renderError() : React.createElement(CommitsTableRowDetails, <CommitsTableRowDetails.Props>{
        commit: this.props.commit,
        detailsLevel: this.state.detailsLevel,
        diff: this.state.diff
      })
    );
  }

  renderError() {
    return DOM.tr({className: 'details-row error'},
      DOM.td({colSpan: 3}, this.state.error)
    );
  }

  private changeDetailsLevel(detailsLevel: string) {
    if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.props.diffProvider.getDiff(this.props.commit.hash)
        .then(diff => this.setState(
          {
            detailsLevel: detailsLevel,
            diff: diff,
            error: null
          })
      ).catch(err => {
          this.setState({detailsLevel: detailsLevel, error: err.message});
        });
    } else {
      this.setState({detailsLevel: detailsLevel, error: null});
    }
  }
}

module CommitsTableRow {
  export interface Props extends CommitsTableRowProps {
  }
}

export = CommitsTableRow;
