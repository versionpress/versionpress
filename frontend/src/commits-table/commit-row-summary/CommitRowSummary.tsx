/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import * as classNames from 'classnames';

import Author from './Author';
import Checkbox from './Checkbox';
import CreateDate from './CreateDate';
import Environment from './Environment';
import Message from './Message';
import DetailsLevel from '../../enums/DetailsLevel';
import * as portal from '../../common/portal';
import { UndoDisabledDialog, UndoMergeDialog } from '../../common/revert-dialog/revertDialog';

interface CommitRowSummaryProps {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  detailsLevel: DetailsLevel;
  onUndo(hash: string, message: string): void;
  onRollback(hash: string, date: string): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

export default class CommitRowSummary extends React.Component<CommitRowSummaryProps, {}> {

  onRowClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const { commit, detailsLevel, onDetailsLevelChange } = this.props;

    if (commit.isEnabled) {
      onDetailsLevelChange(detailsLevel === DetailsLevel.None ? DetailsLevel.Overview : DetailsLevel.None);
    }
  };

  onCheckboxClick = (e: React.MouseEvent) => {
    e.stopPropagation();

    const { commit, isSelected, onCommitsSelect } = this.props;

    onCommitsSelect([commit], !isSelected, e.shiftKey);
  };

  onDetailsLevelClick = (e: React.MouseEvent, detailsLevel: DetailsLevel) => {
    e.stopPropagation();

    this.props.onDetailsLevelChange(detailsLevel);
  };

  onUndoClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();

    const { commit, enableActions, onUndo } = this.props;

    if (commit.isMerge) {
      this.renderUndoMergeDialog();
      return;
    }

    if (enableActions) {
      onUndo(commit.hash, commit.message)
    } else {
      this.renderDisabledDialog();
    }
  };

  onRollbackClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();

    const { commit, enableActions, onRollback } = this.props;

    if (enableActions) {
      onRollback(commit.hash, commit.date)
    } else {
      this.renderDisabledDialog();
    }
  };


  private renderUndoMergeDialog() {
    portal.alertDialog(
      'This is a merge commit',
      <UndoMergeDialog />
    );
  }

  private renderDisabledDialog() {
    portal.alertDialog(
      <span>Undo <em>{this.props.commit.message}</em>?</span>,
      <UndoDisabledDialog />
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
        <Message
          commit={commit}
          detailsLevel={detailsLevel}
          onDetailsLevelClick={this.onDetailsLevelClick}
        />
        <td className='column-actions'>
          {(commit.canUndo || commit.isMerge) && commit.isEnabled
            ? <a
                className={undoClassName}
                href='#'
                onClick={this.onUndoClick}
                title={commit.isMerge
                        ? 'Merge commit cannot be undone.'
                        : !enableActions
                          ? 'You have uncommitted changes in your WordPress directory.'
                          : null
                      }
              >Undo this</a>
            : null
          }
          {commit.canRollback && commit.isEnabled
            ? <a
                className={rollbackClassName}
                href='#'
                onClick={this.onRollbackClick}
                title={!enableActions
                        ? 'You have uncommitted changes in your WordPress directory.'
                        : null
                      }
              >Roll back to this</a>
            : ''
          }
        </td>
      </tr>
    );
  }

}
