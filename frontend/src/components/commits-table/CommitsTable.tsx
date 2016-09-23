/// <reference path='../common/Commits.d.ts' />

import * as React from 'react';
import * as moment from 'moment';
import { observer } from 'mobx-react';

import Row from './row/Row';
import Footer from './footer/Footer';
import Header from './header/Header';
import Note from './note/Note';
import { revertDialog } from '../portal/portal';
import { findIndex } from '../../utils/ArrayUtils';

import CommitRow from '../../stores/CommitRow';
import store from '../../stores/commitsTableStore';

import './CommitsTable.less';

@observer
export default class CommitsTable extends React.Component<{}, {}> {

  onSelectAllChange = (isChecked: boolean) => {
    this.onCommitsSelect(store.commits, isChecked, false);
  };

  onUndo = (hash: string, message: string) => {
    const title = (
      <span>Undo <em>{message}</em>?</span>
    );

    revertDialog(title, () => store.undoCommits([hash]));
  };

  onRollback = (hash: string, date: string) => {
    const title = (
      <span>Roll back to <em>{moment(date).format('LLL')}</em>?</span>
    );

    revertDialog(title, () => store.rollbackToCommit(hash));
  };

  onCommitsSelect = (commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    store.selectCommits(commitsToSelect, isChecked, isShiftKey);
  };

  renderRow = (commitRow: CommitRow, displayNotAbleNote: boolean) => {
    const row = (
      <Row
        commitRow={commitRow}
        enableActions={store.enableActions}
        onUndo={this.onUndo}
        onRollback={this.onRollback}
        onCommitsSelect={this.onCommitsSelect}
        key={commitRow.commit.hash}
      />
    );

    if (displayNotAbleNote) {
      return [
        <Note key='note'>
          VersionPress is not able to undo changes made before it has been activated.
        </Note>,
        row,
      ];
    }

    return [row];
  };

  render() {
    const {
      pages,
      commits,
      commitRows,
      enableActions,
      selectableCommits,
      areAllCommitsSelected,
    } = store;

    const notAbleNoteIndex = findIndex(commits, (commit: Commit, index: number) => (
      !commit.isEnabled && index < commits.length - 1
    ));
    const bla = store.visualizationData;

    return (
      <table className='vp-table widefat fixed'>
        <Header
          areAllCommitsSelected={areAllCommitsSelected}
          selectableCommitsCount={selectableCommits.length}
          enableActions={enableActions}
          onSelectAllChange={this.onSelectAllChange}
        />
        {commitRows.map((commitRow: CommitRow, index: number) => (
          this.renderRow(commitRow, index === notAbleNoteIndex)
        ))}
        <Footer pages={pages} />
      </table>
    );
  }

}
