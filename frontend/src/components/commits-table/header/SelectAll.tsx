import * as React from 'react';
import { observer } from 'mobx-react';

interface SelectAllProps {
  isSelected: boolean;
  selectableCommitsCount: number;
  enableActions: boolean;
  onChange(isChecked: boolean): void;
}

const SelectAll: React.StatelessComponent<SelectAllProps> = (props) => {
  const {
    isSelected,
    selectableCommitsCount,
    enableActions,
    onChange,
  } = props;

  if (selectableCommitsCount === 0) {
    return <div className='column-cb' />;
  }

  return (
    <div className='column-cb manage-column check-column'>
      <label
        className='screen-reader-text'
        htmlFor='CommitsTable-selectAll'
      >
        Select All
      </label>
      <input
        type='checkbox'
        id='CommitsTable-selectAll'
        disabled={!enableActions}
        checked={isSelected}
        onChange={() => onChange(!isSelected)}
      />
    </div>
  );
};

export default observer(SelectAll);
