/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';

interface BinaryFileInfoProps {
  diff: Diff;
}

const BinaryFileInfo: React.StatelessComponent<BinaryFileInfoProps> = ({ diff }) => {
  let message;

  if (diff.from === '/dev/null') {
    message = 'Added empty file';
  } else {
    message = 'Removed empty file';
  }

  return (
    <div className='binary-file-info'>
      {message}
    </div>
  );
};

export default BinaryFileInfo;
