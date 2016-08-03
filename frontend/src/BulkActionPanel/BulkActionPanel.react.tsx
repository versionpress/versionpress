/// <reference path='../Commits/Commits.d.ts' />

import * as React from 'react';
import * as classNames from 'classnames';

import './BulkActionPanel.less';

interface BulkActionPanelProps extends React.Props<JSX.Element> {
  enableActions: boolean;
  onBulkAction: (action: string) => void;
  onClearSelection: () => void;
  selectedCommits: Commit[];
}

export default class BulkActionPanel extends React.Component<BulkActionPanelProps, {}> {

  onBulkAction = (e: React.MouseEvent) => {
    e.preventDefault();
    const value = (this.refs['action'] as HTMLSelectElement).value;
    if (value === '-1') {
      return;
    }

    this.props.onBulkAction(value);
  };

  onClearSelection = (e: React.MouseEvent) => {
    e.preventDefault();
    this.props.onClearSelection();
  };

  render() {
    const { selectedCommits, enableActions } = this.props;

    const noteClassName = classNames({
      'BulkActionPanel-note': true,
      'hide': selectedCommits.length === 0,
    });

    return (
      <div className='BulkActionPanel'>
        <div className='alignleft actions bulkactions'>
          <label htmlFor='BulkActionPanel-selector-top' className='screen-reader-text'>Select bulk action</label>
          <select ref='action' name='action' id='BulkActionPanel-selector-top'>
            <option value='-1'>Bulk Actions</option>
            <option value='undo'>Undo</option>
          </select>
          <input
            type='submit'
            id='BulkActionPanel-doaction'
            className='button action'
            value='Apply'
            onClick={this.onBulkAction}
            disabled={!enableActions || selectedCommits.length === 0}
          />
          <div className={noteClassName}>
            ({selectedCommits.length} {selectedCommits.length === 1 ? 'change' : 'changes'} selected;{' '}
            <a className='BulkActionPanel-clear' href='#' onClick={this.onClearSelection}>clear selection</a>
            )
          </div>
        </div>
      </div>
    );
  }

}
