/// <reference path='../common/Commits.d.ts' />

import * as React from 'react';

import CommitInfo from './commit-info/CommitInfo';
import Footer from './footer/Footer';
import Header from './header/Header';
import NotAbleNote from './not-able-note/NotAbleNote';
import { indexOf } from '../utils/CommitUtils';

import './CommitsTable.less';

interface CommitsTableProps {
  pages: number[];
  commits: Commit[];
  selectedCommits: Commit[];
  enableActions: boolean;
  diffProvider: {getDiff(hash: string): Promise<string>};
  onUndo(hash: string, message: string): void;
  onRollback(hash: string, date: string): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
}

export default class CommitsTable extends React.Component<CommitsTableProps, {}> {

  onSelectAllChange = (isChecked: boolean) => {
    const { commits, onCommitsSelect } = this.props;

    onCommitsSelect(commits, isChecked, false);
  };

  renderCommitInfo(commit: Commit, index: number, displayNotAbleNote: boolean) {
    const {
      selectedCommits,
      enableActions,
      diffProvider,
      onUndo,
      onRollback,
      onCommitsSelect,
    } = this.props;

    const body = (
      <CommitInfo
        commit={commit}
        enableActions={enableActions}
        isSelected={indexOf(selectedCommits, commit) !== -1}
        onUndo={onUndo}
        onRollback={onRollback}
        onCommitsSelect={onCommitsSelect}
        diffProvider={diffProvider}
        key={commit.hash}
      />
    );

    if (displayNotAbleNote) {
      return [
        <NotAbleNote key='note' />,
        body,
      ];
    }

    return body;
  }

  render() {
    const {
      pages,
      commits,
      selectedCommits,
      enableActions,
    } = this.props;

    const notAbleNoteIndex = commits.findIndex((commit: Commit, index: number) => (
      !commit.isEnabled && index < commits.length - 1)
    );

    return (
      <table className='vp-table widefat fixed'>
        <Header
          commits={commits}
          selectedCommits={selectedCommits}
          enableActions={enableActions}
          onSelectAllChange={this.onSelectAllChange}
        />
        {commits.map((commit: Commit, index: number) => (
          this.renderCommitInfo(commit, index, index === notAbleNoteIndex)
        ))}
        <Footer pages={pages} />
      </table>
    );
  }

}
