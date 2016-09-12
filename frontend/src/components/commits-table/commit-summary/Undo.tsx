import * as React from 'react';
import * as classNames from 'classnames';

interface UndoProps {
  commit: Commit;
  enableActions: boolean;
  onClick(e: React.MouseEvent): void;
}

const Undo: React.StatelessComponent<UndoProps> = ({ commit, enableActions, onClick }) => {
  const undoClassName = classNames({
    'vp-table-undo': true,
    'disabled': commit.isMerge || !enableActions,
  });

  const title = commit.isMerge
    ? 'Merge commit cannot be undone.'
    : !enableActions
      ? 'You have uncommitted changes in your WordPress directory.'
      : null;

  return (
    <a
      className={undoClassName}
      href='#'
      onClick={onClick}
      title={title}
    >
      Undo this
    </a>
  );
};

export default Undo;
