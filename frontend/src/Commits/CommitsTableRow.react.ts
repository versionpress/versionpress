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
  detailsLevel: string;
  diff?: string;
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
      React.createElement(CommitsTableRowDetails, <CommitsTableRowDetails.Props>{
        commit: this.props.commit,
        detailsLevel: this.state.detailsLevel,
        diff: this.state.diff
      })
    );
  }

  private changeDetailsLevel(detailsLevel: string) {
    if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.props.diffProvider.getDiff(this.props.commit.hash)
        .then(diff => this.setState(
          {
            detailsLevel: detailsLevel,
            diff: diff
          }
        )
      );
    } else {
      this.setState({detailsLevel: detailsLevel});
    }
  }
}

module CommitsTableRow {
  export interface Props extends CommitsTableRowProps {
  }
}

export = CommitsTableRow;
