/// <reference path='../../../interfaces/State.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

interface OptionProps {
  option: BulkActionPanelOption;
}

const Option: React.StatelessComponent<OptionProps> = ({ option }) => (
  <option value={option.value}>
    {option.title}
  </option>
);

export default observer(Option);
