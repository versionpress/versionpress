/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import moment = require('moment');

import DiffPanel = require('./DiffPanel.react');

const DOM = React.DOM;

interface CommitsTableRowDetailsProps {
  commit: Commit;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  details: string;
}

class CommitsTableRowDetails extends React.Component<CommitsTableRowDetailsProps, {}> {

  constructor() {
    super();
    this.state = {display: 'none'};
  }

  render() {
    if (this.props.commit === null || this.props.details === 'none') {
      return DOM.tr(null);
    }
    const commit = this.props.commit;
    const className = 'alternate details-row' + (commit.isEnabled ? '' : 'disabled');
    const detailsClass = 'details show';

    const overviewTable = DOM.table(null, commit.changes.map((change: Change) => {
      return DOM.tr(null, DOM.td(null, change.type), DOM.td(null, change.action), DOM.td(null, change.name));
    }));

    const overviewRow = DOM.tr({className: className},
      DOM.td(null),
      DOM.td(null,
        DOM.div({className: detailsClass}, overviewTable)
      ),
      DOM.td(null)
    );

    const fullDiffRow = DOM.tr({className: className},
      DOM.td({colSpan: 3},
        DOM.div({className: detailsClass},
          this.props.details === 'overview' ? overviewTable : null,
          this.props.details === 'full-diff' ? React.createElement(DiffPanel, {ref: 'full-diff'}) : null
        )
      )
    );

    return this.props.details === 'overview' ? overviewRow : fullDiffRow;
  }

}

module CommitsTableRowDetails {
  export interface Props extends CommitsTableRowDetailsProps {
  }
}

export = CommitsTableRowDetails;
