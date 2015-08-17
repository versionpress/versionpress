/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import DiffParser = require('../common/DiffParser');

const DOM = React.DOM;


class DiffPanel extends React.Component<any, any> {

  render() {
    var rawDiff = ["0f7caffe34fb49ce0612c7071829be820e32ad16",
      " frontend/src/Commits/Commits.d.ts                |  7 ++++",
      " frontend/src/Commits/CommitsTable.less           |  5 +++",
      " frontend/src/Commits/CommitsTableRow.react.ts    | 16 +++++++-",
      " plugins/versionpress/src/Api/VersionPressApi.php | 48 +++++++++++++++++++++++-",
      " 4 files changed, 73 insertions(+), 3 deletions(-)",
      " ",
      "diff --git a/frontend/src/Commits/Commits.d.ts b/frontend/src/Commits/Commits.d.ts",
      "index 89e2e44..1f98fd9 100644",
      "--- a/frontend/src/Commits/Commits.d.ts",
      "+++ b/frontend/src/Commits/Commits.d.ts",
      "@@ -5,4 +5,11 @@ interface Commit {",
      "   canUndo: boolean;",
      "   canRollback: boolean;",
      "   isEnabled: boolean;",
      "+  changes: Change[];",
      "+}",
      "+",
      "+interface Change {",
      "+  type: string;",
      "+  action: string;",
      "+  name: string;",
      " }",
      "diff --git a/frontend/src/Commits/CommitsTable.less b/frontend/src/Commits/CommitsTable.less",
      "index 9c0333f..71a823f 100644",
      "--- a/frontend/src/Commits/CommitsTable.less",
      "+++ b/frontend/src/Commits/CommitsTable.less",
      "@@ -62,6 +62,10 @@",
      "     border-bottom: 1px solid #ccc;",
      "   }",
      " ",
      "+  td {",
      "+    cursor: pointer;",
      "+  }",
      "+",
      "   .column-date {",
      "     width: 130px;",
      "   }",
      "@@ -83,6 +87,7 @@",
      "   tr {",
      "     .disabled {",
      "       opacity: 0.5;",
      "+      cursor: auto;",
      "     }",
      "   }",
      " ",
      "diff --git a/frontend/src/Commits/CommitsTableRow.react.ts b/frontend/src/Commits/CommitsTableRow.react.ts",
      "index 41983b4..c728c7b 100644",
      "--- a/frontend/src/Commits/CommitsTableRow.react.ts",
      "+++ b/frontend/src/Commits/CommitsTableRow.react.ts",
      "@@ -14,19 +14,31 @@ interface CommitsTableRowProps {",
      " ",
      " class CommitsTableRow extends React.Component<CommitsTableRowProps, any>  {",
      " ",
      "+  public state = {d: false};",
      "+",
      "   render() {",
      "     if (this.props.commit === null) {",
      "       return DOM.tr(null);",
      "     }",
      "     const commit = this.props.commit;",
      "     const className = 'alternate ' + (commit.isEnabled ? '' : 'disabled');",
      "+    const detailsClass = 'details ' + (this.state.d === true ? 'show' : 'hide');",
      "+",
      "+    const details = DOM.table(null, commit.changes.map((change: Change) => {",
      "+      return DOM.tr(null, DOM.td(null, change.type), DOM.td(null, change.action), DOM.td(null, change.name));",
      "+    }));",
      " ",
      "-    return DOM.tr({className: className},",
      "+    return DOM.tr({className: className, onClick: () => this.setState({d: !this.state.d})},",
      "       DOM.td({",
      "         className: 'column-date',",
      "         title: moment(commit.date).format('LLL')",
      "       }, moment(commit.date).fromNow()),",
      "-      DOM.td({className: 'column-message'}, commit.message),",
      "+      DOM.td({className: 'column-message'},",
      "+        DOM.span({}, commit.message),",
      "+        DOM.div({className: detailsClass},",
      "+          DOM.strong({}, 'Details:'),",
      "+            DOM.div({}, details))",
      "+      ),",
      "       DOM.td({className: 'column-actions'},",
      "         commit.canUndo && commit.isEnabled",
      "           ? DOM.a({",
      "diff --git a/plugins/versionpress/src/Api/VersionPressApi.php b/plugins/versionpress/src/Api/VersionPressApi.php",
      "index 98048c4..7cd7d16 100644",
      "--- a/plugins/versionpress/src/Api/VersionPressApi.php",
      "+++ b/plugins/versionpress/src/Api/VersionPressApi.php",
      "@@ -3,6 +3,9 @@",
      " namespace VersionPress\\Api;",
      " ",
      " use VersionPress\\ChangeInfos\\ChangeInfoMatcher;",
      "+use VersionPress\\ChangeInfos\\EntityChangeInfo;",
      "+use VersionPress\\ChangeInfos\\OptionChangeInfo;",
      "+use VersionPress\\ChangeInfos\\PluginChangeInfo;",
      " use VersionPress\\DI\\VersionPressServices;",
      " use VersionPress\\Git\\GitLogPaginator;",
      " use VersionPress\\Git\\GitRepository;",
      "@@ -130,13 +133,27 @@ class VersionPressApi {",
      "             $changeInfo = ChangeInfoMatcher::buildChangeInfo($commit->getMessage());",
      "             $isEnabled = $canUndoCommit || $canRollbackToThisCommit || $commit->getHash() === $initialCommitHash;",
      " ",
      "+",
      "+            $changedFiles = $commit->getChangedFiles();",
      "+            $fileChanges = array_map(function ($changedFile) {",
      "+                $status = $changedFile['status'];",
      "+                $filename = $changedFile['path'];",
      "+",
      "+                return array(",
      "+                    'type' => 'file',",
      "+                    'action' => $status === 'A' ? 'add' : ($status === 'M' ? 'modify' : 'delete'),",
      "+                    'name' => $filename,",
      "+                );",
      "+            }, $changedFiles);",
      "+",
      "             $result[] = array(",
      "                 \"hash\" => $commit->getHash(),",
      "                 \"date\" => $commit->getDate()->format('c'),",
      "                 \"message\" => $changeInfo->getChangeDescription(),",
      "                 \"canUndo\" => $canUndoCommit,",
      "                 \"canRollback\" => $canRollbackToThisCommit,",
      "-                \"isEnabled\" => $isEnabled",
      "+                \"isEnabled\" => $isEnabled,",
      "+                \"changes\" => array_merge($this->convertChangeInfoList($changeInfo->getChangeInfoList()), $fileChanges),",
      "             );",
      "             $isFirstCommit = false;",
      "         }",
      "@@ -265,4 +282,33 @@ class VersionPressApi {",
      "             array('status' => $error['status'])",
      "         );",
      "     }",
      "+",
      "+    private function convertChangeInfoList($getChangeInfoList) {",
      "+        return array_map(array($this, 'convertChangeInfo'), $getChangeInfoList);",
      "+    }",
      "+",
      "+    private function convertChangeInfo($changeInfo) {",
      "+        $change = array();",
      "+",
      "+        if ($changeInfo instanceof EntityChangeInfo) {",
      "+            $change = array(",
      "+                'type' => $changeInfo->getEntityName(),",
      "+                'action' => $changeInfo->getAction(),",
      "+                'name' => $changeInfo->getEntityId(),",
      "+            );",
      "+        }",
      "+",
      "+        if ($changeInfo instanceof PluginChangeInfo) {",
      "+            $pluginTags = $changeInfo->getCustomTags();",
      "+            $pluginName = $pluginTags[PluginChangeInfo::PLUGIN_NAME_TAG];",
      "+",
      "+            $change = array(",
      "+                'type' => 'plugin',",
      "+                'action' => $changeInfo->getAction(),",
      "+                'name' => $pluginName,",
      "+            );",
      "+        }",
      "+",
      "+        return $change;",
      "+    }",
      " }"].join("\n");

    let diffs = DiffParser.parse(rawDiff);

    return DOM.div(null,
      diffs.map(diff =>
        DOM.div({className: 'diff'},
          DOM.h4({className: 'heading'}, (diff.from === '/dev/null' ? diff.to : diff.from).substr(2)), this.formatChunks(diff.chunks)
        )
      )
    );
  }

  private createTableFromChunk(chunk) {
    let [left, right] = this.lr(chunk);

    let mapTwoArrays = (a1: any[], a2: any[], fn: (a: any, b: any) => any) => {
      let result = [];
      for(let i = 0; i < a1.length; i++) {
        result.push(fn(a1[i], a2[i]));
      }
      return result;
    };

    return DOM.table({className: 'chunk'},
      DOM.tbody(null,
        mapTwoArrays(left, right, (l, r) =>
          DOM.tr({className: 'line'},
            DOM.td({className: 'line-left ' + l.type}, this.replaceLeadingSpacesWithHardSpaces(l.content)),
            DOM.td({className: 'line-separator'}),
            DOM.td({className: 'line-right ' + r.type}, this.replaceLeadingSpacesWithHardSpaces(r.content))
          )
        )
      )
    )
  }

  private lr(chunk) {
    let lines = chunk.lines;
    let left = [];
    let right = [];

    for (let i = 0; i < lines.length; i++) {
      let line = lines[i];
      if (line.type === 'unchanged') {
        let missingLines = left.length - right.length;
        for (let j = 0; j < missingLines; j++) {
          right.push({type: 'empty', content: ''});
        }

        for (let j = 0; j < -missingLines; j++) {
          left.push({type: 'empty', content: ''});
        }

        left.push(line);
        right.push(line);
      } else if (line.type === 'removed') {
        let missingLines = left.length - right.length;
        for (let j = 0; j < missingLines; j++) {
          right.push({type: 'empty', content: ''});
        }
        for (let j = 0; j < -missingLines; j++) {
          left.push({type: 'empty', content: ''});
        }

        left.push(line);
      } else if (line.type === 'added') {
        right.push(line);
      }
    }

    return [left, right];
  }

  private formatChunks(chunks: any[]) {
    let result = [];
    let chunkTables = chunks.map(chunk =>
        this.createTableFromChunk(chunk)
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

  private replaceLeadingSpacesWithHardSpaces(content: string): string {
    let match = content.match(/^( +)/); // all leading spaces
    if (!match) {
      return content;
    }

    let numberOfSpaces = match[1].length;
    return "\u00a0".repeat(numberOfSpaces) + content.substr(numberOfSpaces);
  }
}

export = DiffPanel;
