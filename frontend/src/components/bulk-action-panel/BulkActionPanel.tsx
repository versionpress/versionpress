/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
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

interface BulkActionPanelState {
  options: BulkActionPanelOption[];
}

@observer
export default class BulkActionPanel extends React.Component<BulkActionPanelProps, BulkActionPanelState> {

  state = {
    options: [
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
    ],
  };

  onSelectedValueChange = (newValue: string) => {
    const newOptions = this.state.options.map(option => {
      return {
        title: option.title,
        value: option.value,
        isSelected: option.value === newValue,
      };
    });

    this.setState({
      options: newOptions,
    });
  }

  onSubmit = () => {
    const selectedOption = this.state.options.find(option => option.isSelected);

    if (selectedOption.value === '-1') {
      return;
    }

    this.props.onBulkAction(selectedOption.value);
  }

  render() {
    const { changesCount, enableActions, onClearSelection } = this.props;
    const { options } = this.state;

    return (
      <div className='BulkActionPanel'>
        <div className='alignleft actions bulkactions'>
          <Title htmlFor='BulkActionPanel-selector-top' />
          <Options
            options={options}
            id='BulkActionPanel-selector-top'
            onChange={this.onSelectedValueChange}
          />
          <Submit
            onSubmit={this.onSubmit}
            isDisabled={!enableActions || changesCount === 0}
          />
          <ClearSelection
            changesCount={changesCount}
            onClearSelection={onClearSelection}
          />
        </div>
      </div>
    );
  }

}
