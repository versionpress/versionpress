import * as React from 'react';
import * as classNames from 'classnames';

interface ClearSelectionProps {
  changes: number;
  onClick(e: React.MouseEvent): void;
}

const ClearSelection: React.StatelessComponent<ClearSelectionProps> = ({ changes, onClick }) => {
  const noteClassName = classNames({
    'BulkActionPanel-note': true,
    'hide': changes === 0,
  });

  return (
    <div className={noteClassName}>
      {`(${changes} ${changes === 1 ? 'change' : 'changes'} selected; `}
      <a
        className='BulkActionPanel-clear'
        href='#'
        onClick={onClick}
      >
        clear selection
      </a>
      {')'}
    </div>
  );
};

export default ClearSelection;
