/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';

import ChunkTableRow from './ChunkTableRow';
import {
  divideToLeftAndRightColumn,
  mapTwoArrays,
  replaceLeadingSpacesWithHardSpaces,
  highlightInlineDiff,
} from './utils';

interface ChunkTableProps {
  chunk: Chunk;
}

const ChunkTable: React.StatelessComponent<ChunkTableProps> = ({ chunk }) => {
  const [left, right] = divideToLeftAndRightColumn(chunk);

  return (
    <table className='chunk'>
      <tbody>
        {mapTwoArrays(left, right, renderRow)}
      </tbody>
    </table>
  );
};

function renderRow(left: Line, right: Line, index: number) {
  let leftContent: any = replaceLeadingSpacesWithHardSpaces(left.content);
  let rightContent: any = replaceLeadingSpacesWithHardSpaces(right.content);

  if (left.type === 'removed' && right.type === 'added') {
    [leftContent, rightContent] = highlightInlineDiff(leftContent, rightContent);
  }

  return (
    <ChunkTableRow
      leftLineContent={leftContent}
      leftLineType={left.type}
      rightLineContent={rightContent}
      rightLineType={right.type}
      key={index}
    />
  );
}

export default ChunkTable;
