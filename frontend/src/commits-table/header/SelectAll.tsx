import * as React from 'react';
import * as _ from 'lodash';

interface SelectAllProps {
  commits: Commit[];
  selectedCommits: Commit[];
  enableActions: boolean;
  onChange(isChecked: boolean): void;
}

const SelectAll: React.StatelessComponent<SelectAllProps> = (props) => {
  const {
    commits,
    selectedCommits,
    enableActions,
    onChange,
  } = props;

  const selectableCommits = commits.filter((commit: Commit) => commit.canUndo);
  const displaySelectAll = commits.some((commit: Commit) => commit.canUndo);

  if (!displaySelectAll) {
    return <th className='column-cb' />;
  }

  const allSelected = !_.differenceBy(selectableCommits, selectedCommits, ((value: Commit) => value.hash)).length;
  const isChecked = commits.length > 0 && allSelected;

  return (
    <th className='column-cb manage-column check-column'>
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
        checked={isChecked}
        onChange={() => onChange(!isChecked)}
      />
    </th>
  );
};

export default SelectAll;
