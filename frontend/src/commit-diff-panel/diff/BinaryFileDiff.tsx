/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';

interface BinaryFileDiffProps {
  diff: Diff;
}

const getMessage = (diff: Diff) => {
  if (diff.from === '/dev/null') {
    return 'Added binary file';
  } else if (diff.to === '/dev/null') {
    return 'Deleted binary file';
  } else {
    return 'Changed binary file';
  }
};

const BinaryFileDiff: React.StatelessComponent<BinaryFileDiffProps> = ({ diff }) => (
  <div className='binary-file-info'>
    {getMessage(diff)}
  </div>
);

export default BinaryFileDiff;
