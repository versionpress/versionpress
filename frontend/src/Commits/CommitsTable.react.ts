/// <reference path='../../typings/typings.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');
import CommitsTableRow = require('./CommitsTableRow.react');
import CommitsTableNote = require('./CommitsTableNote.react');
import config = require('../config');

require('./CommitsTable.less');

const DOM = React.DOM;
const routes = config.routes;

interface CommitsTableProps {
  currentPage: number;
  pages: number[];
  commits: Commit[];
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  diffProvider: {getDiff: (hash: string) => Promise<string>};
}

class CommitsTable extends React.Component<CommitsTableProps, {}>  {

  private refreshInterval;

  componentDidMount(): void {
    this.refreshInterval = setInterval(() => this.forceUpdate(), 60 * 1000);
  }

  componentWillUnmount(): void {
    clearInterval(this.refreshInterval);
  }

  render() {
    const firstCommit = this.props.commits[0];
    const displayTopNote = firstCommit && !firstCommit.isEnabled;

    return DOM.table({className: 'vp-table widefat fixed'},
      DOM.thead(null,
        DOM.tr(null,
          DOM.th({className: 'column-date'}, 'Date'),
          DOM.th({className: 'column-message'}, 'Message'),
          DOM.th({className: 'column-actions'})
        )
      ),
      displayTopNote
        ? this.renderNote()
        : null
      ,
      this.props.commits.map((commit: Commit, index: number) => {
        const row = React.createElement(CommitsTableRow, <CommitsTableRow.Props> {
          key: commit.hash,
          commit: commit,
          onUndo: this.props.onUndo,
          onRollback: this.props.onRollback,
          diffProvider: this.props.diffProvider
        });

        if (commit.isInitial && index < this.props.commits.length - 1) {
          return [
            row,
            this.renderNote()
          ];
        }
        return row;
      }),
      DOM.tfoot(null,
        DOM.tr(null,
          DOM.td({className: 'vp-table-pagination', colSpan: 3},
            this.props.pages.map((page: number) => {
              return React.createElement(ReactRouter.Link, <ReactRouter.LinkProp> {
                activeClassName: 'active',
                key: page,
                to: page === 1
                  ? routes.home
                  : routes.page,
                params: page === 1
                  ? null
                  : { page: page }
              }, page);
            })
          )
        )
      )
    );
  }

  renderNote() {
    return React.createElement(CommitsTableNote, <CommitsTableNote.Props> {
      key: 'note',
      message: 'VersionPress is not able to undo changes made before it has been activated.'
    });
  }

}

module CommitsTable {
  export interface Props extends CommitsTableProps {}
}

export = CommitsTable;
