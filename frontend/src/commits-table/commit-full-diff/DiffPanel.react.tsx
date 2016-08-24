/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';
import * as JsDiff from 'diff';

import BinaryFileDiff from './BinaryFileDiff';

import DiffParser from '../../common/DiffParser';

import './DiffPanel.less';

interface DiffPanelProps {
  diff: string;
}

export default class DiffPanel extends React.Component<DiffPanelProps, {}> {

  private static createTableFromChunk(chunk: Chunk, i: number) {
    const [left, right] = DiffPanel.divideToLeftAndRightColumn(chunk);

    const mapTwoArrays = function<T, U>(a1: T[], a2: U[], fn: (a: T, b: U, i: number) => any) {
      let result = [];
      for (let i = 0; i < a1.length; i++) {
        result.push(fn(a1[i], a2[i], i));
      }
      return result;
    };

    return (
      <table className='chunk' key={i}>
        <tbody>
          {mapTwoArrays(left, right, (l, r, i) => {
            let leftContent: any = DiffPanel.replaceLeadingSpacesWithHardSpaces(l.content);
            let rightContent: any = DiffPanel.replaceLeadingSpacesWithHardSpaces(r.content);

            if (l.type === 'removed' && r.type === 'added') {
              [leftContent, rightContent] = this.highlightInlineDiff(leftContent, rightContent);
            }

            return (
              <tr className='line' key={i}>
                <td className={'line-left ' + l.type}>{leftContent}</td>
                <td className='line-separator' />
                <td className={'line-right ' + r.type}>{rightContent}</td>
              </tr>
            );
          })}
        </tbody>
      </table>
    );
  }

  private static highlightInlineDiff(leftContent: string, rightContent: string) {
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
  }

  private static divideToLeftAndRightColumn(chunk: Chunk): [Line[], Line[]] {
    const { lines } = chunk;
    let left: Line[] = [];
    let right: Line[] = [];

    for (let i = 0; i < lines.length; i++) {
      const line = lines[i];
      if (line.type === 'unchanged') {
        [left, right] = DiffPanel.balanceLeftAndRightColumn(left, right);

        left.push(line);
        right.push(line);
      } else if (line.type === 'removed') {
        [left, right] = DiffPanel.balanceLeftAndRightColumn(left, right);

        left.push(line);
      } else if (line.type === 'added') {
        right.push(line);
      }
    }

    [left, right] = DiffPanel.balanceLeftAndRightColumn(left, right);

    return [left, right];
  }

  private static balanceLeftAndRightColumn(left: Line[], right: Line[]): [Line[], Line[]] {
    const missingLines = left.length - right.length;

    for (let j = 0; j < missingLines; j++) {
      right.push({
        type: 'empty',
        content: '',
      });
    }

    for (let j = 0; j < -missingLines; j++) {
      left.push({
        type: 'empty',
        content: '',
      });
    }

    return [left, right];
  }

  private static formatInfoForPlainFileDiff(diff: Diff) {
    const { chunks } = diff;
    let result = [];

    if (chunks.length === 0) {
      let message;
      if (diff.from === '/dev/null') {
        message = 'Added empty file';
      } else {
        message = 'Removed empty file';
      }

      result.push(<div className='binary-file-info'>{message}</div>);
      return result;
    }

    const chunkTables = chunks.map((chunk, i) =>
      DiffPanel.createTableFromChunk(chunk, i)
    );

    for (let i = 0; i < chunkTables.length; i++) {
      result.push(chunkTables[i]);
      if (chunkTables[i + 1]) {
        result.push(
          <table className='chunk-separator' key={'sep' + i}>
            <tbody>
              <tr className='line'>
                <td className='line-left'><span className='hellip'>&middot;&middot;&middot;</span></td>
                <td className='line-separator' />
                <td className='line-right'><span className='hellip'>&middot;&middot;&middot;</span></td>
              </tr>
              <tr className='line'>
                <td className='line-left' />
                <td className='line-separator' />
                <td className='line-right' />
              </tr>
            </tbody>
          </table>
        );
      }
    }

    return result;
  }

  private static replaceLeadingSpacesWithHardSpaces(content: string): string {
    const match = content.match(/^( +)/); // All leading spaces
    if (!match) {
      return content;
    }

    const numberOfSpaces = match[1].length;
    return '\u00a0'.repeat(numberOfSpaces) + content.substr(numberOfSpaces);
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
              ? DiffPanel.formatInfoForPlainFileDiff(diff)
              : <BinaryFileDiff diff={diff} />
            }
          </div>
        )}
      </div>
    );
  }

}
