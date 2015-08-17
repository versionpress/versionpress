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
}

interface CommitsTableRowState {
  displayDetails: string;
}

class CommitsTableRow extends React.Component<CommitsTableRowProps, CommitsTableRowState> {

  constructor() {
    super();
    this.state = {displayDetails: 'none'};
  }

  render() {
    return DOM.tbody(null,
      React.createElement(CommitsTableRowSummary, <CommitsTableRowSummary.Props>{
        commit: this.props.commit,
        onUndo: this.props.onUndo,
        onRollback: this.props.onRollback,
        onDetailsLevelChanged: details => this.setState({displayDetails: details}),
        details: this.state.displayDetails
      }),
      React.createElement(CommitsTableRowDetails, <CommitsTableRowDetails.Props>{
        commit: this.props.commit,
        details: this.state.displayDetails
      })
    );
  }

  private changeDetailsLevel(detailsLevel: string) {
    this.setState({displayDetails: detailsLevel});
  }
}

module CommitsTableRow {
  export interface Props extends CommitsTableRowProps {
  }
}

export = CommitsTableRow;
