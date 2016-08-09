import * as React from 'react';

interface OptionProps {
  option: BulkActionPanelOption;
}

const Option: React.StatelessComponent<OptionProps> = ({ option }) => (
  <option value={option.value}>
    {option.title}
  </option>
);

export default Option;
