/// <reference path='../../typings/typings.d.ts' />
/// <reference path='../Commits/Commits.d.ts' />

import * as React from 'react';

import './BulkActionPanel.less';

interface BulkActionPanelProps extends React.Props<JSX.Element> {
  enableActions: boolean;
  onBulkAction: (string) => void;
  onClearSelection: () => void;
  selected: Commit[];
}

export default class BulkActionPanel extends React.Component<BulkActionPanelProps, {}> {

  onBulkAction(e: MouseEvent) {
    e.preventDefault();
    const value = (this.refs['action'] as HTMLSelectElement).value;
    if (value === '-1') {
      return;
    }

    this.props.onBulkAction(value);
  }

  onClearSelection(e: MouseEvent) {
    e.preventDefault();
    this.props.onClearSelection();
  }

  render() {
    const selected = this.props.selected;

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
            onClick={this.onBulkAction.bind(this)}
            disabled={!this.props.enableActions || selected.length === 0}
          />
          <div className={'BulkActionPanel-note' + (selected.length === 0 ? ' hide' : '')}>
            ({selected.length} {selected.length === 1 ? 'change' : 'changes'} selected;{' '}
            <a className='BulkActionPanel-clear' href="#" onClick={this.onClearSelection.bind(this)}>clear selection</a>
            )
          </div>
        </div>
      </div>
    );
  }

}
