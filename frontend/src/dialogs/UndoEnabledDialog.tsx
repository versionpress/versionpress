import * as React from 'react';

const UndoEnabledDialog: React.StatelessComponent<{}> = () => (
  <div>
    <p>
      For Early Access releases, please have a backup. {' '}
      <a
        href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback'
        target='_blank'
      >
        Learn more about reverts.
      </a>
    </p>
  </div>
);

export default UndoEnabledDialog;
