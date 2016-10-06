import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

interface RollbackProps {
  enableActions: boolean;
  onClick(e: React.MouseEvent): void;
}

const Rollback: React.StatelessComponent<RollbackProps> = ({ enableActions, onClick }) => {
  const rollbackClassName = classNames({
    'vp-table-rollback': true,
    'disabled': !enableActions,
  });

  const title = !enableActions
    ? 'You have uncommitted changes in your WordPress directory.'
    : null;

  return (
    <a
      className={rollbackClassName}
      href='#'
      onClick={onClick}
      title={title}
    >
      Roll back to this
    </a>
  );
};

export default observer(Rollback);
