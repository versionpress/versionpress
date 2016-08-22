/// <reference path='../common/Commits.d.ts' />

import * as React from 'react';

import Body from './body/Body';
import Footer from './footer/Footer';
import Header from './header/Header';
import NotAbleNote from './not-able-note/NotAbleNote';
import { indexOf } from '../utils/CommitUtils';

import './CommitsTable.less';

interface CommitsTableProps extends React.Props<JSX.Element> {
  currentPage: number;
  pages: number[];
  commits: Commit[];
  selectedCommits: Commit[];
  enableActions: boolean;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  diffProvider: {getDiff(hash: string): Promise<string>};
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
}

export default class CommitsTable extends React.Component<CommitsTableProps, {}>  {

  private refreshInterval;

  componentDidMount(): void {
    this.refreshInterval = setInterval(() => this.forceUpdate(), 60 * 1000);
  }

  componentWillUnmount(): void {
    clearInterval(this.refreshInterval);
  }

  onSelectAllChange = (isChecked: boolean) => {
    this.props.onCommitsSelect(this.props.commits, isChecked, false);
  };

  render() {
    const {
      pages,
      commits,
      selectedCommits,
      enableActions,
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
            <Body
              commit={commit}
              enableActions={this.props.enableActions}
              isSelected={indexOf(this.props.selectedCommits, commit) !== -1}
              onUndo={this.props.onUndo}
              onRollback={this.props.onRollback}
              onCommitSelect={this.props.onCommitsSelect}
              diffProvider={this.props.diffProvider}
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
