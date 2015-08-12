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

  public state = {displayDetails: false};

  render() {
    if (this.props.commit === null) {
      return DOM.tr(null);
    }
    const commit = this.props.commit;
    const className = 'alternate ' + (commit.isEnabled ? '' : 'disabled');
    const detailsClass = 'details ' + (this.state.displayDetails === true ? 'show' : 'hide');

    const details = DOM.table(null, commit.changes.map((change: Change) => {
      return DOM.tr(null, DOM.td(null, change.type), DOM.td(null, change.action), DOM.td(null, change.name));
    }));

    return DOM.tr({className: className, onClick: () => this.setState({displayDetails: !this.state.displayDetails})},
      DOM.td({
        className: 'column-date',
        title: moment(commit.date).format('LLL')
      }, moment(commit.date).fromNow()),
      DOM.td({className: 'column-message'},
        DOM.span({}, commit.message),
        DOM.div({className: detailsClass},
          DOM.strong({}, 'Details:'),
            DOM.div({}, details))
      ),
      DOM.td({className: 'column-actions'},
        commit.canUndo && commit.isEnabled
          ? DOM.a({
              className: 'vp-table-undo',
              href: '#',
              onClick: this.props.onUndo,
              'data-hash': commit.hash,
              'data-message': commit.message
            }, 'Undo this')
          : '',
        commit.canRollback && commit.isEnabled
          ? DOM.a({
              className: 'vp-table-rollback',
              href: '#',
              onClick: this.props.onRollback,
              'data-hash': commit.hash,
              'data-date': commit.date
            }, 'Roll back to this')
          : ''
      )
    );
  }

}

module CommitsTableRow {
  export interface Props extends CommitsTableRowProps {}
}

export = CommitsTableRow;
