/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import moment = require('moment');

import DiffPanel = require('./DiffPanel.react');

const DOM = React.DOM;

interface CommitsTableRowDetailsProps {
  commit: Commit;
  detailsLevel: string;
  diff?: string;
  loading?: boolean;
}

class CommitsTableRowDetails extends React.Component<CommitsTableRowDetailsProps, {}> {

  render() {
    if (this.props.commit === null || this.props.detailsLevel === 'none') {
      return DOM.tr(null);
    }
    const commit = this.props.commit;
    const className = 'alternate details-row' + (commit.isEnabled ? '' : 'disabled') + (this.props.loading ? ' loading' : '');
    const detailsClass = 'details';

    const overviewTable = DOM.table(null, commit.changes.map((change: Change) => {
      return DOM.tr(null, DOM.td(null, change.type), DOM.td(null, change.action), DOM.td(null, change.name));
    }));

    const overviewElement = DOM.div({className: detailsClass + ' overview'}, overviewTable);

    const fullDiffElement = DOM.div({className: detailsClass},
      React.createElement(DiffPanel, <DiffPanel.Props>{diff: this.props.diff})
    );

    return DOM.tr({className: className},
      DOM.td({colSpan: 3},
        this.props.detailsLevel === 'overview' ? overviewElement : fullDiffElement
      )
    );
  }

}

module CommitsTableRowDetails {
  export interface Props extends CommitsTableRowDetailsProps {
  }
}

export = CommitsTableRowDetails;
