import * as React from 'react';

import SelectAll from './SelectAll';

interface HeaderProps {
  commits: Commit[];
  selectedCommits: Commit[];
  enableActions: boolean;
  onSelectAllChange(isChecked: boolean): void;
}

const Header: React.StatelessComponent<HeaderProps> = (props) => {
  const {
    commits,
    selectedCommits,
    enableActions,
    onSelectAllChange,
  } = props;

  return (
    <thead>
      <tr>
        <th className='column-environment'/>
        <SelectAll
          commits={commits}
          selectedCommits={selectedCommits}
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
