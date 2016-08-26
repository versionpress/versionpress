import * as React from 'react';

interface BinaryFileDiffProps {
  diff: Diff;
}

const BinaryFileDiff: React.StatelessComponent<BinaryFileDiffProps> = ({ diff }) => {
  let message;

  if (diff.from === '/dev/null') {
    message = 'Added binary file';
  } else if (diff.to === '/dev/null') {
    message = 'Deleted binary file';
  } else {
    message = 'Changed binary file';
  }

  return (
    <div className='binary-file-info'>
      {message}
    </div>
  );
};

export default BinaryFileDiff;
