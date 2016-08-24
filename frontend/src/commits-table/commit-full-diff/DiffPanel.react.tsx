/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';

import BinaryFileDiff from './BinaryFileDiff';
import BinaryFileInfo from './BinaryFileInfo';
import ChunkTable from './ChunkTable';
import ChunkSeparator from './ChunkSeparator';

import DiffParser from '../../common/DiffParser';

import './DiffPanel.less';

interface DiffPanelProps {
  diff: string;
}

export default class DiffPanel extends React.Component<DiffPanelProps, {}> {

  private renderPlainFileDiff(diff: Diff) {
    const { chunks } = diff;
    let result = [];

    if (chunks.length === 0) {
      result.push(
        <BinaryFileInfo
          diff={diff}
          key="binary-file-info"
        />
      );

      return result;
    }

    const chunkTables = chunks.map((chunk: Chunk, i) => (
      <ChunkTable
        chunk={chunk}
        key={i}
      />
    ));

    for (let i = 0; i < chunkTables.length; i++) {
      result.push(chunkTables[i]);
      if (chunkTables[i + 1]) {
        result.push(
          <ChunkSeparator key={`sep${i}`} />
        );
      }
    }

    return result;
  }

  render() {
    const { diff } = this.props;

    if (diff === null) {
      return <div />;
    }

    const diffs = DiffParser.parse(diff);

    return (
      <div>
        {diffs.map((diff: Diff, i) =>
          <div className='DiffPanel' key={i}>
            <h4 className='heading'>{(diff.from === '/dev/null' ? diff.to : diff.from).substr(2)}</h4>
            {diff.type === 'plain'
              ? this.renderPlainFileDiff(diff)
              : <BinaryFileDiff diff={diff} />
            }
          </div>
        )}
      </div>
    );
  }

}
