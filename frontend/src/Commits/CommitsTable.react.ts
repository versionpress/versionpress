/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');
import CommitsTableRow = require('./CommitsTableRow.react');
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
}

class CommitsTable extends React.Component<CommitsTableProps, any>  {

  render() {
    const prevPage = this.props.currentPage - 1;
    const nextPage = this.props.currentPage + 1;

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
          DOM.td({className: 'vp-table-pagination', colSpan: 2},
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
          ),
          DOM.td({className: 'vp-table-pagination-actions'},
            this.props.pages.indexOf(prevPage) !== -1
              ? React.createElement(ReactRouter.Link, <ReactRouter.LinkProp> {
                to: prevPage === 1
                  ? routes.home
                  : routes.page,
                params: prevPage === 1
                  ? null
                  : { page: prevPage }
              }, '< prev')
              : null,
            this.props.pages.indexOf(nextPage) !== -1
              ? React.createElement(ReactRouter.Link, <ReactRouter.LinkProp> {
                to: nextPage === 1
                  ? routes.home
                  : routes.page,
                params: nextPage === 1
                  ? null
                  : { page: nextPage }
              }, 'next >')
              : null
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
