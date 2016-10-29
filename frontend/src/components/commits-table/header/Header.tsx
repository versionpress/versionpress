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

  const visualisationWidth = showVisualisation ? branches * 20 : 20;

  return (
    <div className="vp-table-header" style={{ flex: '1 0 100%', display: 'flex', flexFlow: 'row nowrap', alignItems: 'center' }}>
      <div
        className='column-environment'
        onClick={onToggleShowVisualisation}
        style={{ flex: `${visualisationWidth}px`, cursor: 'pointer', display: 'flex', maxWidth: `${visualisationWidth}px` }}
      >
        {canToggleVisualisation &&
          <span style={{ paddingLeft: 5, fontSize: '100%', fontWeight: 'bold', margin: 'auto' }}>
            {showVisualisation ? '<' : '>'}
          </span>
        }
      </div>
      <SelectAll
        isSelected={areAllCommitsSelected}
        selectableCommitsCount={selectableCommitsCount}
        enableActions={enableActions}
        onChange={onSelectAllChange}
      />
      <div className='column-date'>
        Date
      </div>
      <div className='column-author'/>
      <div className='column-message'>
        Message
      </div>
      <div className='column-actions'/>
    </div>
  );
};

export default observer(Header);
