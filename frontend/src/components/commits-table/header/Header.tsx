import * as React from 'react';
import { observer } from 'mobx-react';

import SelectAll from './SelectAll';

interface HeaderProps {
  areAllCommitsSelected: boolean;
  selectableCommitsCount: number;
  enableActions: boolean;
  canToggleVisualisation: boolean;
  showVisualisation: boolean;
  branches: number;
  onSelectAllChange(isChecked: boolean): void;
  onToggleShowVisualisation(): void;
}

const Header: React.StatelessComponent<HeaderProps> = (props) => {
  const {
    areAllCommitsSelected,
    selectableCommitsCount,
    enableActions,
    canToggleVisualisation,
    showVisualisation,
    onSelectAllChange,
    branches,
    onToggleShowVisualisation,
  } = props;

  return (
    <thead>
      <tr>
        <th
          className='column-environment'
          onClick={onToggleShowVisualisation}
          style={{ width: showVisualisation ? branches * 20 : 20, cursor: 'pointer' }}
        >
          {canToggleVisualisation &&
            <span style={{ paddingLeft: 5, fontSize: '100%', fontWeight: 'bold' }}>
              {showVisualisation ? '<' : '>'}
            </span>
          }
        </th>
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

export default observer(Header);
