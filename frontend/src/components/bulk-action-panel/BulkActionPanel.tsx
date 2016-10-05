/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
import { observable } from 'mobx';
import { observer } from 'mobx-react';

import Title from './Title';
import Options from './options/Options';
import Submit from './Submit';
import ClearSelection from './ClearSelection';

import './BulkActionPanel.less';

interface BulkActionPanelProps {
  changesCount: number;
  enableActions: boolean;
  onBulkAction(action: string): void;
  onClearSelection(): void;
}

@observer
export default class BulkActionPanel extends React.Component<BulkActionPanelProps, {}> {

  @observable options: BulkActionPanelOption[] = [
    {
      title: 'Bulk Actions',
      value: '-1',
      isSelected: true,
    },
    {
      title: 'Undo',
      value: 'undo',
      isSelected: false,
    },
  ];

  onSelectedValueChange = (newValue: string) => {
    const newOptions = this.options.map(option => {
      return {
        title: option.title,
        value: option.value,
        isSelected: option.value === newValue,
      };
    });

    this.options = newOptions;
  };

  onSubmitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const selectedOption = this.options.find(option => option.isSelected);

    if (selectedOption.value === '-1') {
      return;
    }

    this.props.onBulkAction(selectedOption.value);
  };

  onClearSelectionClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.props.onClearSelection();
  };

  render() {
    const { changesCount, enableActions } = this.props;

    return (
      <div className='BulkActionPanel'>
        <div className='alignleft actions bulkactions'>
          <Title htmlFor='BulkActionPanel-selector-top' />
          <Options
            options={this.options}
            id='BulkActionPanel-selector-top'
            onChange={this.onSelectedValueChange}
          />
          <Submit
            onClick={this.onSubmitClick}
            isDisabled={!enableActions || changesCount === 0}
          />
          <ClearSelection
            changesCount={changesCount}
            onClick={this.onClearSelectionClick}
          />
        </div>
      </div>
    );
  }

}
