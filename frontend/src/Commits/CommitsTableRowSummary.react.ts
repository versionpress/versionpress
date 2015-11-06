/// <reference path='../../typings/typings.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import moment = require('moment');
import portal = require('../common/portal');

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
        (commit.canUndo || commit.isMerge) && commit.isEnabled
          ? DOM.a({
            className: 'vp-table-undo ' + (commit.isMerge ? 'disabled' : ''),
            href: '#',
            onClick: commit.isMerge
              ? (e) => { this.renderUndoMergeDialog(); e.stopPropagation(); }
              : (e) => { this.props.onUndo(e); e.stopPropagation(); },
            title: commit.isMerge
              ? 'Merge commit cannot be undone.'
              : null,
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

  private renderUndoMergeDialog() {
    const body = DOM.p(null,
      'Merge commit is a special type of commit that cannot be undone. ',
      DOM.a({
          href: 'http://docs.versionpress.net/en/feature-focus/undo-and-rollback#merge-commits',
          target: '_blank'
        }, 'Learn more'
      )
    );
    portal.alertDialog('This is a merge commit', body);
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
