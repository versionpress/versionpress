/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');

import CommitOverview = require('./CommitOverview.react');
import DiffPanel = require('./DiffPanel.react');

const DOM = React.DOM;

interface CommitsTableRowDetailsProps {
  commit: Commit;
  detailsLevel: string;
  diff?: string;
  loading?: boolean;
}

class CommitsTableRowDetails extends React.Component<CommitsTableRowDetailsProps, {}> {

  constructor() {
    super();
    this.state = {display: 'none'};
  }

  render() {
    if (this.props.commit === null || this.props.detailsLevel === 'none') {
      return DOM.tr(null);
    }
    const commit = this.props.commit;
    const className = 'details-row' + (commit.isEnabled ? '' : 'disabled') + (this.props.loading ? ' loading' : '');
    const detailsClass = 'details';

    const overview = React.createElement(CommitOverview, <CommitOverview.Props>{commit: commit});

    const overviewRow = DOM.tr({className: className},
      DOM.td(null),
      DOM.td(null,
        this.props.loading ? DOM.div({className: 'details-row-loader'}, null) : null,
        DOM.div({className: detailsClass}, overview)
      ),
      DOM.td(null)
    );

    const fullDiffRow = DOM.tr({className: className},
      DOM.td({colSpan: 3},
        DOM.div({className: detailsClass},
          React.createElement(DiffPanel, <DiffPanel.Props>{diff: this.props.diff})
        )
      )
    );

    return this.props.detailsLevel === 'overview' ? overviewRow : fullDiffRow;
  }

}

module CommitsTableRowDetails {
  export interface Props extends CommitsTableRowDetailsProps {}
}

export = CommitsTableRowDetails;
