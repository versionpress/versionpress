/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import * as classNames from 'classnames';

import Author from './Author';
import Checkbox from './Checkbox';
import CreateDate from './CreateDate';
import Environment from './Environment';
import DetailsLevel from '../../enums/DetailsLevel';
import * as portal from '../../common/portal';
import { UndoDisabledDialog } from '../../common/revert-dialog/revertDialog';

interface CommitRowSummaryProps {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  detailsLevel: DetailsLevel;
  onUndo(e): void;
  onRollback(e): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

export default class CommitRowSummary extends React.Component<CommitRowSummaryProps, {}> {

  onCheckboxClick = (e: React.MouseEvent) => {
    e.stopPropagation();

    const { commit, isSelected, onCommitsSelect } = this.props;

    onCommitsSelect([commit], !isSelected, e.shiftKey);
  };

  onDetailsLevelClick = (e: React.MouseEvent, detailsLevel: DetailsLevel) => {
    e.stopPropagation();

    this.props.onDetailsLevelChange(detailsLevel);
  };

  onRowClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const { commit, detailsLevel, onDetailsLevelChange } = this.props;

    if (commit.isEnabled) {
      onDetailsLevelChange(detailsLevel === DetailsLevel.None ? DetailsLevel.Overview : DetailsLevel.None);
    }
  };

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
      'displayed-details': detailsLevel !== DetailsLevel.None,
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
        <Environment environment={commit.environment} />
        <Checkbox
          isVisible={commit.canUndo}
          isChecked={isSelected}
          isDisabled={!enableActions}
          onClick={this.onCheckboxClick}
        />
        <CreateDate date={commit.date} />
        <Author author={commit.author} />
        <td className='column-message'>
          {commit.isMerge
            ? <span className='merge-icon' title='Merge commit'>M</span>
            : null
          }
          {this.renderMessage(commit.message)}
          {detailsLevel !== DetailsLevel.None
            ? <div className='detail-buttons'>
                <button
                  className='button'
                  disabled={detailsLevel === DetailsLevel.Overview}
                  onClick={e => this.onDetailsLevelClick(e, DetailsLevel.Overview)}
                >Overview</button>
                <button
                  className='button'
                  disabled={detailsLevel === DetailsLevel.FullDiff}
                  onClick={e => this.onDetailsLevelClick(e, DetailsLevel.FullDiff)}
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
