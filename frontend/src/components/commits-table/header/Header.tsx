import * as React from 'react';

import SelectAll from './SelectAll';

interface HeaderProps {
  areAllCommitsSelected: boolean;
  selectableCommitsCount: number;
  enableActions: boolean;
  onSelectAllChange(isChecked: boolean): void;
}

const Header: React.StatelessComponent<HeaderProps> = (props) => {
  const {
    areAllCommitsSelected,
    selectableCommitsCount,
    enableActions,
    onSelectAllChange,
  } = props;

  return (
    <thead>
      <tr>
        <th className='column-environment'/>
        <SelectAll
          isSelected={areAllCommitsSelected}
          selectableCommitsCount={selectableCommitsCount}
          enableActions={enableActions}
          onChange={onSelectAllChange}
        />
        <th className='column-date'>
          Date
        </th>
        <th className='column-author'/>
        <th className='column-message'>
          Message
        </th>
        <th className='column-actions'/>
      </tr>
    </thead>
  );
};

export default Header;
