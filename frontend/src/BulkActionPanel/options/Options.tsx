import * as React from 'react';

import Option from './Option';

interface OptionsProps {
  options: BulkActionPanelOption[];
  id: string;
  onChange(newValue: string): void;
}

export default class Options extends React.Component<OptionsProps, {}> {

  onChange = (e) => {
    this.props.onChange(e.target.value);
  }

  render() {
    const { options, id } = this.props;

    return (
      <select
        name="action"
        id={id}
        onChange={this.onChange}
      >
        {options.map(option => {
          return (
            <Option
              option={option}
              key={option.value}
            />
          );
        })}
      </select>
    );
  }

}
