/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import moment = require('moment');

const DOM = React.DOM;

interface CommitsTableRowProps {
  commit: Commit;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
}

class CommitsTableRow extends React.Component<CommitsTableRowProps, any>  {

  render() {
    if (this.props.commit === null) {
      return DOM.tr(null);
    }
    const commit = this.props.commit;

    return DOM.tr({className: (commit.isEnabled ? '' : 'disabled')},
      DOM.td({
        className: 'column-date',
        title: moment(commit.date).format('LLL')
      }, moment(commit.date).fromNow()),
      DOM.td({className: 'column-message'}, commit.message),
      DOM.td({className: 'column-actions'},
        commit.canUndo && commit.isEnabled
          ? DOM.a({
              className: 'vp-table-undo',
              href: '#',
              onClick: this.props.onUndo,
              'data-hash': commit.hash
            }, 'Undo')
          : '',
        commit.canRollback && commit.isEnabled
          ? DOM.a({
              className: 'vp-table-rollback',
              href: '#',
              onClick: this.props.onRollback,
              'data-hash': commit.hash
            }, 'Rollback to commit')
          : ''
      )
    );
  }

}

module CommitsTableRow {
  export interface Props extends CommitsTableRowProps {}
}

export = CommitsTableRow;
