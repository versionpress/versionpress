/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import BinaryFileInfo from './BinaryFileInfo';
import ChunkSeparator from './ChunkSeparator';
import ChunkTable from '../chunk-table/ChunkTable';

interface PlainFileDiffProps {
  diff: Diff;
}

const PlainFileDiff: React.StatelessComponent<PlainFileDiffProps> = ({ diff }) => {
  const { chunks } = diff;

  if (chunks.length === 0) {
    return <BinaryFileInfo diff={diff} />;
  }

  const chunkTables = chunks.map((chunk: Chunk, i) => (
    <ChunkTable chunk={chunk} key={i} />
  ));

  let plainFileDiff = [];

  for (let i = 0; i < chunkTables.length; i++) {
    plainFileDiff.push(chunkTables[i]);
    if (chunkTables[i + 1]) {
      plainFileDiff.push(<ChunkSeparator key={`sep${i}`} />);
    }
  }

  return (
    <div>
      {plainFileDiff}
    </div>
  );
};

export default observer(PlainFileDiff);
