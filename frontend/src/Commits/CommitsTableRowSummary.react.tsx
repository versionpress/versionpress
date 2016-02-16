/// <reference path='../../typings/typings.d.ts' />
/// <reference path='./Commits.d.ts' />

import * as React from 'react';
import * as moment from 'moment';
import * as portal from '../common/portal';
import {UndoDisabledDialog} from '../Commits/revertDialog';

interface CommitsTableRowSummaryProps extends React.Props<JSX.Element> {
  commit: Commit;
  enableActions: boolean;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  onDetailsLevelChanged: (detailsLevel) => any;
  detailsLevel: string;
}

export default class CommitsTableRowSummary extends React.Component<CommitsTableRowSummaryProps, {}> {

  render() {
    if (this.props.commit === null) {
      return null;
    }
    const commit = this.props.commit;
    const className = (commit.isEnabled ? '' : 'disabled') + (this.props.detailsLevel !== 'none' ? ' displayed-details' : '');

    return (
      <tr className={className} onClick={() => this.toggleDetails()}>
        <td className='column-date' title={moment(commit.date).format('LLL')}>{moment(commit.date).fromNow()}</td>
        <td className='column-message'>
          {commit.isMerge
            ? <span className='merge-icon' title='Merge commit'>M</span>
            : null
          }
          {this.renderMessage(commit.message)}
          {this.props.detailsLevel !== 'none'
            ? <div className='detail-buttons'>
                <button
                  className='button'
                  disabled={this.props.detailsLevel === 'overview'}
                  onClick={(e) => { this.changeDetailsLevel('overview'); e.stopPropagation(); }}
                >Overview</button>
                <button
                  className='button'
                  disabled={this.props.detailsLevel === 'full-diff'}
                  onClick={(e) => { this.changeDetailsLevel('full-diff'); e.stopPropagation(); }}
                >Full diff</button>
              </div>
            : null
          }
        </td>
        <td className='column-actions'>
          {(commit.canUndo || commit.isMerge) && commit.isEnabled
            ? <a
                className={'vp-table-undo ' + (commit.isMerge || !this.props.enableActions ? 'disabled' : '')}
                href='#'
                onClick={commit.isMerge
                          ? (e) => { this.renderUndoMergeDialog(); e.stopPropagation(); }
                          : this.props.enableActions
                            ? (e) => { this.props.onUndo(e); e.stopPropagation(); }
                            : (e) => { this.renderDisabledDialog(); e.stopPropagation(); }
                        }
                title={commit.isMerge
                        ? 'Merge commit cannot be undone.'
                        : !this.props.enableActions
                          ? 'You have uncommitted changes in your WordPress directory.'
                          : null
                      }
                data-hash={commit.hash}
                data-message={commit.message}
              >Undo this</a>
            : null
          }
          {commit.canRollback && commit.isEnabled
            ? <a
                className={'vp-table-rollback ' + (!this.props.enableActions ? 'disabled' : '')}
                href='#'
                onClick={this.props.enableActions
                          ? (e) => { this.props.onRollback(e); e.stopPropagation(); }
                          : (e) => { this.renderDisabledDialog(); e.stopPropagation(); }
                        }
                title={!this.props.enableActions
                        ? 'You have uncommitted changes in your WordPress directory.'
                        : null
                      }
                data-hash={commit.hash}
                data-date={commit.date}
              >Roll back to this</a>
            : ''
          }
        </td>
      </tr>
    );
  }

  private renderUndoMergeDialog() {
    const body = (
      <p>
        Merge commit is a special type of commit that cannot be undone. {' '}
        <a
          href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#merge-commits'
          target='_blank'
        >Learn more</a>
      </p>
    );
    portal.alertDialog('This is a merge commit', body);
  }

  private renderDisabledDialog() {
    const title = <span>Undo <em>{this.props.commit.message}</em>?</span>;
    const body = <UndoDisabledDialog />;
    portal.alertDialog(title, body);
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
      return <span>{message}</span>;
    }
    return (
      <span>
        {messageChunks[1] !== '' ? this.renderMessage(messageChunks[1]) : null}
        {messageChunks[2] !== '' ? <span className='identifier'>{messageChunks[2]}</span> : null}
        {messageChunks[3] !== '' ? this.renderMessage(messageChunks[3]) : null}
      </span>
    );
  }
}
