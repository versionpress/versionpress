/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import DiffParser = require('../common/DiffParser');
import JsDiff = require('diff');

const DOM = React.DOM;

interface DiffPanelProps {
  diff: string;
}

class DiffPanel extends React.Component<DiffPanelProps, any> {

  render() {
    let diffs = DiffParser.parse(this.props.diff);

    return DOM.div(null,
      diffs.map(diff =>
        DOM.div({className: 'diff'},
          DOM.h4({className: 'heading'}, (diff.from === '/dev/null' ? diff.to : diff.from).substr(2)),
          diff.type === 'plain' ? DiffPanel.formatChunks(diff.chunks) : DiffPanel.formatInfoForBinaryFileDiff(diff)
        )
      )
    );
  }

  private static createTableFromChunk(chunk) {
    let [left, right] = DiffPanel.divideToLeftAndRightColumn(chunk);

    let mapTwoArrays = (a1: any[], a2: any[], fn: (a: any, b: any) => any) => {
      let result = [];
      for(let i = 0; i < a1.length; i++) {
        result.push(fn(a1[i], a2[i]));
      }
      return result;
    };

    return DOM.table({className: 'chunk'},
      DOM.tbody(null,
        mapTwoArrays(left, right, (l, r) => {
            let leftContent: any = DiffPanel.replaceLeadingSpacesWithHardSpaces(l.content);
            let rightContent: any = DiffPanel.replaceLeadingSpacesWithHardSpaces(r.content);

            if (l.type === 'removed' && r.type === 'added') {
              [leftContent, rightContent] = this.highlightInlineDiff(leftContent, rightContent);
            }

            return DOM.tr({className: 'line'},
              DOM.td({className: 'line-left ' + l.type}, leftContent),
              DOM.td({className: 'line-separator'}),
              DOM.td({className: 'line-right ' + r.type}, rightContent)
            )
          }
        )
      )
    )
  }

  private static highlightInlineDiff(leftContent, rightContent) {

    let highlightLine = (diffPart: JsDiff.IDiffResult, shouldBeHighlighted: () => boolean, color: string) => {
      if (shouldBeHighlighted()) {
        return DOM.span({style: {backgroundColor: color}}, diffPart.value);
      } else if (!diffPart.added && !diffPart.removed) {
        return DOM.span(null, diffPart.value);
      }

      return DOM.span(null);
    };

    let lineDiff = JsDiff.diffWords(leftContent, rightContent);

    leftContent = lineDiff.map(diffPart => highlightLine(diffPart, () => !!diffPart.removed, '#f8cbcb'));
    rightContent = lineDiff.map(diffPart => highlightLine(diffPart, () => !!diffPart.added, '#a6f3a6'));

    return [leftContent, rightContent];
  }

  private static divideToLeftAndRightColumn(chunk) {
    let lines = chunk.lines;
    let left = [];
    let right = [];

    for (let i = 0; i < lines.length; i++) {
      let line = lines[i];
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

  private static balanceLeftAndRightColumn(left, right) {
    let missingLines = left.length - right.length;
    for (let j = 0; j < missingLines; j++) {
      right.push({type: 'empty', content: ''});
    }
    for (let j = 0; j < -missingLines; j++) {
      left.push({type: 'empty', content: ''});
    }

    return [left, right];
  }

  private static formatChunks(chunks: any[]) {
    let result = [];
    let chunkTables = chunks.map(chunk =>
        DiffPanel.createTableFromChunk(chunk)
    );

    for(let i = 0; i < chunkTables.length; i++) {
      result.push(chunkTables[i]);
      if (chunkTables[i + 1]) {
        result.push(
          DOM.table({className: 'chunk-separator'},
            DOM.tbody(null,
              DOM.tr({className: 'line'},
                DOM.td({className: 'line-left'}, DOM.span({className: 'hellip'}, '\u00B7\u00B7\u00B7')),
                DOM.td({className: 'line-separator'}),
                DOM.td({className: 'line-right'}, DOM.span({className: 'hellip'}, '\u00B7\u00B7\u00B7'))
              ),
              DOM.tr({className: 'line'},
                DOM.td({className: 'line-left'}),
                DOM.td({className: 'line-separator'}),
                DOM.td({className: 'line-right'})
              )
            )
          )
        );
      }
    }

    return result;
  }

  private static replaceLeadingSpacesWithHardSpaces(content: string): string {
    let match = content.match(/^( +)/); // all leading spaces
    if (!match) {
      return content;
    }

    let numberOfSpaces = match[1].length;
    return "\u00a0".repeat(numberOfSpaces) + content.substr(numberOfSpaces);
  }

  private static formatInfoForBinaryFileDiff(diff) {
    var message;

    if (diff.from === '/dev/null') {
      message = 'Added binary file';
    } else if (diff.to === '/dev/null') {
      message = 'Deleted binary file';
    } else {
      message = 'Changed binary file';
    }

    return DOM.div({className: 'binary-file-info'}, message);
  }
}

module DiffPanel {
  export interface Props extends DiffPanelProps {
  }
}

export = DiffPanel;