/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import moment = require('moment');

const DOM = React.DOM;

interface CommitsTableRowSummaryProps {
  commit: Commit;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  onDetailsLevelChanged: (detailsLevel) => any;
  detailsLevel: string;
}

class CommitsTableRowSummary extends React.Component<CommitsTableRowSummaryProps, {}> {

  render() {
    if (this.props.commit === null) {
      return null;
    }
    const commit = this.props.commit;
    const className = (commit.isEnabled ? '' : 'disabled') + (this.props.detailsLevel !== 'none' ? ' displayed-details' : '');

    return DOM.tr({className: className, onClick: () => this.toggleDetails()},
      DOM.td({
        className: 'column-date',
        title: moment(commit.date).format('LLL')
      }, moment(commit.date).fromNow()),
      DOM.td({className: 'column-message'},
        commit.isMerge ? DOM.span({className: 'merge-icon', title: 'Merge commit'}, 'M') : null,
        this.renderMessage(commit.message),
        this.props.detailsLevel !== 'none' ? DOM.div({className: 'detail-buttons'},
          DOM.button({className: 'button', disabled: this.props.detailsLevel === 'overview', onClick: (e) => {
              this.changeDetailsLevel('overview');
              e.stopPropagation();
            }
          }, 'Overview'),
          DOM.button({className: 'button', disabled: this.props.detailsLevel === 'full-diff', onClick: (e) => {
              this.changeDetailsLevel('full-diff');
              e.stopPropagation();
            }
          }, 'Full diff')
        ) : null
      ),
      DOM.td({className: 'column-actions'},
        commit.canUndo && commit.isEnabled
          ? DOM.a({
            className: 'vp-table-undo',
            href: '#',
            onClick: (e) => { this.props.onUndo(e); e.stopPropagation(); },
            'data-hash': commit.hash,
            'data-message': commit.message
          }, 'Undo this')
          : '',
        commit.canRollback && commit.isEnabled
          ? DOM.a({
            className: 'vp-table-rollback',
            href: '#',
            onClick: (e) => { this.props.onRollback(e); e.stopPropagation(); },
            'data-hash': commit.hash,
            'data-date': commit.date
          }, 'Roll back to this')
          : ''
      )
    );
  }

  private toggleDetails() {
    if (this.props.commit.isEnabled) {
      this.props.onDetailsLevelChanged(this.props.detailsLevel === 'none' ? 'overview' : 'none');
    }
  }

  private changeDetailsLevel(detailsLevel) {
    this.props.onDetailsLevelChanged(detailsLevel);
  }

  private renderMessage(message: string) {
    const messageChunks = /(.*)'(.*)'(.*)/.exec(message);
    if (!messageChunks || messageChunks.length < 4) {
      return DOM.span(null, message);
    }
    return DOM.span(null,
      messageChunks[1] !== '' ? this.renderMessage(messageChunks[1]) : null,
      messageChunks[2] !== '' ? DOM.span({className: 'identifier'}, messageChunks[2]) : null,
      messageChunks[3] !== '' ? this.renderMessage(messageChunks[3]) : null
    );
  }
}

module CommitsTableRowSummary {
  export interface Props extends CommitsTableRowSummaryProps {}
}

export = CommitsTableRowSummary;
