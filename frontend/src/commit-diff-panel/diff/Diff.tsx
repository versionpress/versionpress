/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';

import BinaryFileDiff from './BinaryFileDiff';
import PlainFileDiff from './PlainFileDiff';

interface PanelProps {
  diff: Diff;
}

const Panel: React.StatelessComponent<PanelProps> = ({ diff }) => (
  <div className='CommitDiffPanel'>
    <h4 className='heading'>
      {(diff.from === '/dev/null' ? diff.to : diff.from).substr(2)}
    </h4>

    {diff.type === 'plain'
      ? <PlainFileDiff diff={diff} />
      : <BinaryFileDiff diff={diff} />
    }
  </div>
);

export default Panel;
