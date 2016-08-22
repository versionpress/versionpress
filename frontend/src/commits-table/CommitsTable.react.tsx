/// <reference path='../common/Commits.d.ts' />

import * as React from 'react';
import {Link} from 'react-router';

import Header from './header/Header';
import CommitsTableRow from './row/CommitsTableRow.react';
import Note from './Note';
import { indexOf } from '../utils/CommitUtils';
import config from '../config';

import './CommitsTable.less';

const routes = config.routes;

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

    let noteDisplayed = false;

    return (
      <table className='vp-table widefat fixed'>
        <Header
          commits={commits}
          selectedCommits={selectedCommits}
          enableActions={enableActions}
          onSelectAllChange={this.onSelectAllChange}
        />
        {commits.map((commit: Commit, index: number) => {
          const row = <CommitsTableRow
                        key={commit.hash}
                        commit={commit}
                        enableActions={this.props.enableActions}
                        isSelected={indexOf(this.props.selectedCommits, commit) !== -1}
                        onUndo={this.props.onUndo}
                        onRollback={this.props.onRollback}
                        onCommitSelect={this.props.onCommitsSelect}
                        diffProvider={this.props.diffProvider}
                      />;

          if (!noteDisplayed && !commit.isEnabled && index < commits.length - 1) {
            noteDisplayed = true;
            return [
              <Note
                key='note'
                message='VersionPress is not able to undo changes made before it has been activated.'
              />,
              row,
            ];
          }
          return row;
        })}
        <tfoot>
          <tr>
            <td className='vp-table-pagination' colSpan={6}>
              {pages.map((page: number) => {
                return <Link
                          activeClassName='active'
                          key={page}
                          to={page === 1 ? routes.home : routes.page}
                          params={page === 1 ? null : { page: page }}
                        >{page}</Link>;
              })}
            </td>
          </tr>
        </tfoot>
      </table>
    );
  }

}
