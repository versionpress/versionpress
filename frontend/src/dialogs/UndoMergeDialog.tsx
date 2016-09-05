import * as React from 'react';

const UndoMergeDialog: React.StatelessComponent<{}> = () => (
  <div>
    <p>
      Merge commit is a special type of commit that cannot be undone. {' '}
      <a
        href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#merge-commits'
        target='_blank'
      >
        Learn more
      </a>
    </p>
  </div>
);

export default UndoMergeDialog;
