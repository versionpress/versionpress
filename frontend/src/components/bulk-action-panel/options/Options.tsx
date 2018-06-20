/// <reference path='../../../interfaces/State.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import Option from './Option';

interface OptionsProps {
  options: BulkActionPanelOption[];
  id: string;
  onChange(newValue: string): void;
}

@observer
export default class Options extends React.Component<OptionsProps, {}> {

  onChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    this.props.onChange(e.target.value);
  }

  render() {
    const { options, id } = this.props;

    return (
      <select
        name='action'
        id={id}
        onChange={this.onChange}
      >
        {options.map(option => (
          <Option
            option={option}
            key={option.value}
          />
        ))}
      </select>
    );
  }

}
