/// <reference path='../../typings/typings.d.ts' />
/// <reference path='./Commits.d.ts' />

import * as React from 'react';
import * as _ from 'lodash';
import {Link} from 'react-router';
import CommitsTableRow from './CommitsTableRow.react';
import CommitsTableNote from './CommitsTableNote.react';
import {indexOf} from '../Commits/CommitUtils';
import config from '../config';

import './CommitsTable.less';

const routes = config.routes;

interface CommitsTableProps extends React.Props<JSX.Element> {
  currentPage: number;
  pages: number[];
  commits: Commit[];
  selected: Commit[];
  enableActions: boolean;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  onCommitSelect: (commits: Commit[], check: boolean, shiftKey: boolean) => void;
  diffProvider: {getDiff: (hash: string) => Promise<string>};
}

export default class CommitsTable extends React.Component<CommitsTableProps, {}>  {

  private refreshInterval;

  componentDidMount(): void {
    this.refreshInterval = setInterval(() => this.forceUpdate(), 60 * 1000);
  }

  componentWillUnmount(): void {
    clearInterval(this.refreshInterval);
  }

  render() {
    const firstCommit = this.props.commits[0];
    const displayTopNote = firstCommit && !firstCommit.isEnabled;

    return (
      <table className='vp-table widefat fixed'>
        <thead>
          <tr>
            {this.renderSelectAll()}
            <th className='column-date'>Date</th>
            <th className='column-message'>Message</th>
            <th className='column-actions' />
          </tr>
        </thead>
        {displayTopNote
          ? this.renderNote()
          : null
        }
        {this.props.commits.map((commit: Commit, index: number) => {
          const row = <CommitsTableRow
                        key={commit.hash}
                        commit={commit}
                        enableActions={this.props.enableActions}
                        isSelected={indexOf(this.props.selected, commit) !== -1}
                        onUndo={this.props.onUndo}
                        onRollback={this.props.onRollback}
                        onCommitSelect={this.props.onCommitSelect}
                        diffProvider={this.props.diffProvider}
                      />;

          if (commit.isInitial && index < this.props.commits.length - 1) {
            return [
              row,
              this.renderNote()
            ];
          }
          return row;
        })}
        <tfoot>
          <tr>
            <td className='vp-table-pagination' colSpan={4}>
              {this.props.pages.map((page: number) => {
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

  renderNote() {
    return <CommitsTableNote
             key='note'
             message='VersionPress is not able to undo changes made before it has been activated.'
           />;
  }

  renderSelectAll() {
    const selectableCommits = this.props.commits.filter((commit: Commit) => commit.canUndo);
    const displaySelectAll = this.props.commits.some((commit: Commit) => commit.canUndo);

    if (!displaySelectAll) {
      return <td className='column-cb' />
    }

    const allSelected = !_.differenceBy(selectableCommits, this.props.selected, ((value: Commit) => value.hash)).length;
    return (
      <td className='column-cb manage-column check-column'>
        <label className='screen-reader-text' htmlFor='CommitsTable-selectAll'>Select All</label>
        <input
          type='checkbox'
          id='CommitsTable-selectAll'
          checked={this.props.commits.length > 0 && allSelected}
          onChange={this.onSelectAll.bind(this)}
        />
      </td>
    );
  }

  onSelectAll(e: React.MouseEvent) {
    const check = (e.target as HTMLInputElement).checked;
    this.props.onCommitSelect(this.props.commits, check, false);
  }

}
