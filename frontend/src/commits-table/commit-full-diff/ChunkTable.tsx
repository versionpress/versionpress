import * as React from 'react';
import * as JsDiff from 'diff';

import ChunkTableRow from './ChunkTableRow';

interface ChunkTableProps {
  chunk: Chunk;
}

const divideToLeftAndRightColumn = (chunk: Chunk): [Line[], Line[]] => {
  const { lines } = chunk;
  let left: Line[] = [];
  let right: Line[] = [];

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    if (line.type === 'unchanged') {
      [left, right] = balanceLeftAndRightColumn(left, right);

      left.push(line);
      right.push(line);
    } else if (line.type === 'removed') {
      [left, right] = balanceLeftAndRightColumn(left, right);

      left.push(line);
    } else if (line.type === 'added') {
      right.push(line);
    }
  }

  [left, right] = balanceLeftAndRightColumn(left, right);

  return [left, right];
};

const balanceLeftAndRightColumn = (left: Line[], right: Line[]): [Line[], Line[]] => {
  const missingLines = left.length - right.length;

  for (let i = 0; i < missingLines; i++) {
    right.push({
      type: 'empty',
      content: '',
    });
  }

  for (let i = 0; i < -missingLines; i++) {
    left.push({
      type: 'empty',
      content: '',
    });
  }

  return [left, right];
};

const mapTwoArrays = function<T, U>(a1: T[], a2: U[], fn: (a: T, b: U, i: number) => any): any[] {
  let result = [];
  for (let i = 0; i < a1.length; i++) {
    result.push(fn(a1[i], a2[i], i));
  }
  return result;
};

const replaceLeadingSpacesWithHardSpaces = (content: string): string => {
  const match = content.match(/^( +)/); // All leading spaces
  if (!match) {
    return content;
  }

  const numberOfSpaces = match[1].length;
  return '\u00a0'.repeat(numberOfSpaces) + content.substr(numberOfSpaces);
};

const highlightInlineDiff = (leftContent: string, rightContent: string): [JSX.Element[], JSX.Element[]] => {
  const highlightLine = (diffPart: JsDiff.IDiffResult, shouldBeHighlighted: () => boolean, color: string) => {
    if (shouldBeHighlighted()) {
      return <span style={{backgroundColor: color}}>{diffPart.value}</span>;
    } else if (!diffPart.added && !diffPart.removed) {
      return <span>{diffPart.value}</span>;
    }

    return <span />;
  };

  const lineDiff = JsDiff.diffWordsWithSpace(leftContent, rightContent);

  return [
    lineDiff.map(diffPart => highlightLine(diffPart, () => !!diffPart.removed, '#f8cbcb')),
    lineDiff.map(diffPart => highlightLine(diffPart, () => !!diffPart.added, '#a6f3a6')),
  ];
};

const ChunkTable: React.StatelessComponent<ChunkTableProps> = ({ chunk }) => {
  const [left, right] = divideToLeftAndRightColumn(chunk);

  return (
    <table className='chunk'>
      <tbody>
      {mapTwoArrays(left, right, (l, r, i) => {
        let leftContent: any = replaceLeadingSpacesWithHardSpaces(l.content);
        let rightContent: any = replaceLeadingSpacesWithHardSpaces(r.content);

        if (l.type === 'removed' && r.type === 'added') {
          [leftContent, rightContent] = highlightInlineDiff(leftContent, rightContent);
        }

        return (
          <ChunkTableRow
            leftLineContent={leftContent}
            leftLineType={l.type}
            rightLineContent={rightContent}
            rightLineType={r.type}
            key={i}
          />
        );
      })}
      </tbody>
    </table>

  );
};

export default ChunkTable;
