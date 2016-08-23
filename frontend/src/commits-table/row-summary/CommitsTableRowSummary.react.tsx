/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import * as moment from 'moment';
import * as classNames from 'classnames';

import * as portal from '../../common/portal';
import { UndoDisabledDialog } from '../../common/revert-dialog/revertDialog';
import {getGitBranchColor} from '../../services/GitBranchColorProvider';

interface CommitsTableRowSummaryProps extends React.Props<JSX.Element> {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  onCommitsSelect: (commits: Commit[], check: boolean, shiftKey: boolean) => void;
  onDetailsLevelChanged: (detailsLevel) => any;
  detailsLevel: string;
}

export default class CommitsTableRowSummary extends React.Component<CommitsTableRowSummaryProps, {}> {

  onCheckboxClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (!this.props.enableActions) {
      return;
    }

    const target = e.target as HTMLInputElement;
    let checked;

    if (target.tagName === 'INPUT') {
      checked = target.checked;
    } else {
      const checkbox = target.getElementsByTagName('input')[0] as HTMLInputElement;
      checked = !checkbox.checked;
    }
    this.props.onCommitsSelect([this.props.commit], checked, e.shiftKey);
  };

  onDetailsLevelClick = (e: React.MouseEvent, detailsLevel: string) => {
    e.stopPropagation();

    this.props.onDetailsLevelChanged(detailsLevel);
  };

  onRowClick = (e: React.MouseEvent) => {
    e.preventDefault();

    if (this.props.commit.isEnabled) {
      this.props.onDetailsLevelChanged(this.props.detailsLevel === 'none' ? 'overview' : 'none');
    }
  };

  private getAuthorTooltip(commit: Commit) {
    const author = commit.author;
    if (author.name === 'Non-admin action') {
      return 'This action is not associated with any user, e.g., it was a public comment';
    } else if (author.name === 'WP-CLI') {
      return 'This action was done via WP-CLI';
    }

    return author.name + ' <' + author.email + '>';
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

  render() {
    const { commit, enableActions, isSelected, detailsLevel } = this.props;

    if (commit === null) {
      return null;
    }

    const rowClassName = classNames({
      'disabled': !commit.isEnabled,
      'displayed-details': detailsLevel !== 'none',
    });
    const undoClassName = classNames({
      'vp-table-undo': true,
      'disabled': commit.isMerge || !enableActions,
    });
    const rollbackClassName = classNames({
      'vp-table-rollback': true,
      'disabled': !enableActions,
    });

    return (
      <tr className={rowClassName} onClick={this.onRowClick}>
        <td className='column-environment'>
          {commit.environment === '?'
            ? null
            : <div style={{backgroundColor: getGitBranchColor(commit.environment)}}>{commit.environment}</div>
          }
        </td>
        {commit.canUndo
          ? <td className='column-cb' onClick={this.onCheckboxClick}>
              <input
                type='checkbox'
                checked={isSelected}
                disabled={!enableActions}
                readOnly={true}
              />
            </td>
          : <td className='column-cb' />
        }
        <td className='column-date' title={moment(commit.date).format('LLL')}>{moment(commit.date).fromNow()}</td>
        <td className='column-author'>
          <img
            className='avatar'
            src={commit.author.avatar}
            title={this.getAuthorTooltip(commit)}
            width={20}
            height={20}
          />
        </td>
        <td className='column-message'>
          {commit.isMerge
            ? <span className='merge-icon' title='Merge commit'>M</span>
            : null
          }
          {this.renderMessage(commit.message)}
          {detailsLevel !== 'none'
            ? <div className='detail-buttons'>
                <button
                  className='button'
                  disabled={detailsLevel === 'overview'}
                  onClick={e => this.onDetailsLevelClick(e, 'overview')}
                >Overview</button>
                <button
                  className='button'
                  disabled={detailsLevel === 'full-diff'}
                  onClick={e => this.onDetailsLevelClick(e, 'full-diff')}
                >Full diff</button>
              </div>
            : null
          }
        </td>
        <td className='column-actions'>
          {(commit.canUndo || commit.isMerge) && commit.isEnabled
            ? <a
                className={undoClassName}
                href='#'
                onClick={commit.isMerge
                          ? (e) => { this.renderUndoMergeDialog(); e.stopPropagation(); }
                          : enableActions
                            ? (e) => { this.props.onUndo(e); e.stopPropagation(); }
                            : (e) => { this.renderDisabledDialog(); e.stopPropagation(); }
                        }
                title={commit.isMerge
                        ? 'Merge commit cannot be undone.'
                        : !enableActions
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
                className={rollbackClassName}
                href='#'
                onClick={enableActions
                          ? (e) => { this.props.onRollback(e); e.stopPropagation(); }
                          : (e) => { this.renderDisabledDialog(); e.stopPropagation(); }
                        }
                title={!enableActions
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

}
