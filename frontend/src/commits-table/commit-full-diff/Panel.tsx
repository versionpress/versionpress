import * as React from 'react';

import BinaryFileDiff from './BinaryFileDiff';
import BinaryFileInfo from './BinaryFileInfo';
import ChunkTable from './ChunkTable';
import ChunkSeparator from './ChunkSeparator';

interface PanelProps {
  diff: Diff;
}

const getPlainFileDiff = (diff: Diff) => {
  const { chunks } = diff;
  let plainFileDiff = [];

  if (chunks.length === 0) {
    plainFileDiff.push(
      <BinaryFileInfo
        diff={diff}
        key='binary-file-info'
      />
    );

    return plainFileDiff;
  }

  const chunkTables = chunks.map((chunk: Chunk, i) => (
    <ChunkTable
      chunk={chunk}
      key={i}
    />
  ));

  for (let i = 0; i < chunkTables.length; i++) {
    plainFileDiff.push(chunkTables[i]);
    if (chunkTables[i + 1]) {
      plainFileDiff.push(
        <ChunkSeparator key={`sep${i}`} />
      );
    }
  }

  return plainFileDiff;
};

const Panel: React.StatelessComponent<PanelProps> = ({ diff }) => (
  <div className='DiffPanel'>
    <h4 className='heading'>
      {(diff.from === '/dev/null' ? diff.to : diff.from).substr(2)}
    </h4>
    {diff.type === 'plain'
      ? getPlainFileDiff(diff)
      : <BinaryFileDiff diff={diff} />
    }
  </div>
);

export default Panel;
