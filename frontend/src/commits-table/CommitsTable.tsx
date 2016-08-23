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
  onUndo(e): void;
  onRollback(e): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
}

export default class CommitsTable extends React.Component<CommitsTableProps, {}>  {

  private refreshInterval;

  componentDidMount() {
    this.refreshInterval = setInterval(() => this.forceUpdate(), 60 * 1000);
  }

  componentWillUnmount() {
    clearInterval(this.refreshInterval);
  }

  onSelectAllChange = (isChecked: boolean) => {
    const { commits, onCommitsSelect } = this.props;

    onCommitsSelect(commits, isChecked, false);
  };

  render() {
    const {
      pages,
      commits,
      selectedCommits,
      enableActions,
      diffProvider,
      onUndo,
      onRollback,
      onCommitsSelect,
    } = this.props;

    let isNotAbleNoteDisplayed = false;

    return (
      <table className='vp-table widefat fixed'>
        <Header
          commits={commits}
          selectedCommits={selectedCommits}
          enableActions={enableActions}
          onSelectAllChange={this.onSelectAllChange}
        />
        {commits.map((commit: Commit, index: number) => {
          const body = (
            <CommitInfo
              commit={commit}
              enableActions={enableActions}
              isSelected={indexOf(selectedCommits, commit) !== -1}
              onUndo={onUndo}
              onRollback={onRollback}
              onCommitSelect={onCommitsSelect}
              diffProvider={diffProvider}
              key={commit.hash}
            />
          );

          if (!isNotAbleNoteDisplayed && !commit.isEnabled && index < commits.length - 1) {
            isNotAbleNoteDisplayed = true;

            return [
              <NotAbleNote key='note' />,
              body,
            ];
          }

          return body;
        })}
        <Footer pages={pages} />
      </table>
    );
  }

}
