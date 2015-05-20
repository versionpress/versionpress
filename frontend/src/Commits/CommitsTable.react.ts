/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import request = require('superagent');

interface CommitsTableProps {
  commits: Commit[]
}

interface CommitsTableState {
  commits: Commit[]
}

class CommitsTable extends React.Component<CommitsTableProps, CommitsTableState>  {

  constructor() {
    super();
    this.state = {
      commits: []
    };
  }

  componentWillReceiveProps(props: CommitsTableProps) {
    this.setState({
      commits: props.commits
    });
  }

  undoCommit(e) {
    e.preventDefault();
    request
      .get('http://localhost/agilio/wordpress/wp-json/versionpress/undo')
      .query({commits: ['abcde']})
      .set('Accept', 'application/json')
      .end((err: any, res: request.Response) => {
        console.log(res.body);
      });
    console.log('Undo commit');
  }

  rollbackToCommit(e) {
    e.preventDefault();
    request
      .get('http://localhost/agilio/wordpress/wp-json/versionpress/rollback')
      .query({commit: 'abcde'})
      .set('Accept', 'application/json')
      .end((err: any, res: request.Response) => {
        console.log(res.body);
      });
    console.log('Rollback to commit');
  }

  render() {
    return React.DOM.div(null,
      React.DOM.table(null,
        React.DOM.tr(null,
          React.DOM.th(null, "Date"),
          React.DOM.th(null, "Message"),
          React.DOM.th()
        ),
        this.state.commits.map((commit: Commit) => {
          return React.DOM.tr({key: commit.hash},
            React.DOM.td(null, commit.date),
            React.DOM.td(null, commit.message),
            React.DOM.td(null,
              commit.canUndo
                ? React.DOM.a({
                    href: '#',
                    onClick: this.undoCommit.bind(this)
                  }, 'Undo')
                : '',
              commit.canRollback
                ? React.DOM.a({
                    href: '#',
                    onClick: this.rollbackToCommit.bind(this)
                  }, 'Rollback to commit')
                : ''
            )
          )
        })
      )
    );
  }
}

module CommitsTable {
  export interface Props extends CommitsTableProps {}
}

export = CommitsTable;
