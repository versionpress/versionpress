import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

interface UndoProps {
  commit: Commit;
  enableActions: boolean;
  onClick(): void;
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
      : '';

  return (
    <a
      className={undoClassName}
      href='#'
      onClick={e => { e.stopPropagation(); e.preventDefault(); onClick(); }}
      title={title}
    >
      Undo this
    </a>
  );
};

export default observer(Undo);
