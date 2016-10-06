/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

interface BinaryFileInfoProps {
  diff: Diff;
}

const BinaryFileInfo: React.StatelessComponent<BinaryFileInfoProps> = ({ diff }) => (
  <div className='binary-file-info'>
    {diff.from === '/dev/null'
      ? 'Added empty file'
      : 'Removed empty file'
    }
  </div>
);

export default observer(BinaryFileInfo);
