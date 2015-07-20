/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');
import routes = require('../routes');
import CommitsTableRow = require('./CommitsTableRow.react');

require('./CommitsTable.less');

const DOM = React.DOM;

interface CommitsTableProps {
  pages: number[];
  commits: Commit[];
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
}

class CommitsTable extends React.Component<CommitsTableProps, any>  {

  render() {
    return DOM.table({className: 'vp-table widefat fixed'},
      DOM.thead(null,
        DOM.tr(null,
          DOM.th({className: 'column-date'}, 'Date'),
          DOM.th({className: 'column-message'}, 'Message'),
          DOM.th({className: 'column-actions'})
        )
      ),
      DOM.tbody(null,
        this.props.commits.map((commit: Commit) => {
          return React.createElement(CommitsTableRow, <CommitsTableRow.Props> {
            key: commit.hash,
            commit: commit,
            onUndo: this.props.onUndo,
            onRollback: this.props.onRollback
          });
        })
      ),
      DOM.tfoot(null,
        DOM.tr(null,
          DOM.td({className: 'vp-table-pagination', colSpan: 3},
            this.props.pages.map((page: number) => {
              return React.createElement(ReactRouter.Link, <ReactRouter.LinkProp> {
                activeClassName: 'active',
                key: page,
                to: page === 0
                  ? routes.defaultRoute.props.name
                  : routes.pageRoute.props.name,
                params: page === 0
                  ? {}
                  : { page: page }
              }, page);
            })
          )
        )
      )
    );
  }

}

module CommitsTable {
  export interface Props extends CommitsTableProps {}
}

export = CommitsTable;
