/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';
import * as JsDiff from 'diff';

export function divideToLeftAndRightColumn (chunk: Chunk): [Line[], Line[]] {
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
}

export function mapTwoArrays<T, U>(a1: T[], a2: U[], fn: (a: T, b: U, i: number) => any): any[] {
  let result = [];
  for (let i = 0; i < a1.length; i++) {
    result.push(fn(a1[i], a2[i], i));
  }
  return result;
}

export function replaceLeadingSpacesWithHardSpaces(content: string): string {
  const match = content.match(/^( +)/); // All leading spaces
  if (!match) {
    return content;
  }

  const numberOfSpaces = match[1].length;
  return '\u00a0'.repeat(numberOfSpaces) + content.substr(numberOfSpaces);
}

export function highlightInlineDiff(leftContent: string, rightContent: string) {
  const lineDiff = JsDiff.diffWordsWithSpace(leftContent, rightContent);

  return [
    lineDiff.map((diffPart, i) => highlightLine(diffPart, !!diffPart.removed, '#f8cbcb', i)),
    lineDiff.map((diffPart, i) => highlightLine(diffPart, !!diffPart.added, '#a6f3a6', i)),
  ];
}

function balanceLeftAndRightColumn(left: Line[], right: Line[]): [Line[], Line[]] {
  const missingLines = left.length - right.length;

  const emptyLine = {
    type: 'empty',
    content: '',
  };

  for (let i = 0; i < missingLines; i++) {
    right.push(emptyLine);
  }
  for (let i = 0; i < -missingLines; i++) {
    left.push(emptyLine);
  }

  return [left, right];
}

function highlightLine(diffPart: JsDiff.IDiffResult, shouldBeHighlighted: boolean, color: string, index: number) {
  if (shouldBeHighlighted) {
    return (
      <span style={{backgroundColor: color}} key={index}>
        {diffPart.value}
      </span>
    );
  } else if (!diffPart.added && !diffPart.removed) {
    return <span key={index}>{diffPart.value}</span>;
  }

  return <span key={index} />;
}
