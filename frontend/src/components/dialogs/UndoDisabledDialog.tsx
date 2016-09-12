import * as React from 'react';

const UndoDisabledDialog: React.StatelessComponent<{}> = () => (
  <div>
    <p className='undo-warning'>
      <span className='icon icon-warning' />
      You have {' '}
      <a
        href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files'
        target='_blank'
      >
        uncommitted changes
      </a>
      {' '} in your WordPress directory.
      <br />
      Please commit them before doing a revert.
    </p>
  </div>
);

export default UndoDisabledDialog;
