/// <reference path='../common/Commits.d.ts' />
/// <reference path='./CommitOverviewPanel.d.ts' />

import * as ArrayUtils from '../utils/ArrayUtils';

const groupByTag = (changes: Change[], tag: string): PreprocessedLine[] => {
  const metaByTag = ArrayUtils.groupBy(changes, c => c.tags[tag]);
  return Object.keys(metaByTag).map(tagValue => {
    const { type, action } = changes[0];
    return {
      key: type + '-' + action + '-' + tagValue,
      changes: metaByTag[tagValue],
    };
  });
};

const preprocessLinesByTypeAndAction = (changes: Change[], type: string, action: string): PreprocessedLine[] => {
  if (type === 'usermeta') {
    return groupByTag(changes, 'VP-User-Login');
  }
  if (type === 'postmeta') {
    return groupByTag(changes, 'VP-Post-Title');
  }
  if (type === 'versionpress' && (action === 'undo' || action === 'rollback')) {
    return changes.map((change, i) => ({
      key: type + '-' + action + '-' + i,
      changes: [change],
    }));
  }
  if (type === 'comment') {
    return groupByTag(changes, 'VP-Comment-PostTitle');
  }
  if (type === 'post') {
    return groupByTag(changes, 'VP-Post-Type');
  }
  return [{
    key: type + '-' + action,
    changes: changes,
  }];
};

export default (changes: Change[]): PreprocessedLine[] => {
  const changesByTypeAndAction = ArrayUtils.groupBy(changes, change => [change.type, change.action]) as ChangesByTypeAndAction;
  let preprocessedLines = [];

  for (const type in changesByTypeAndAction) {
    for (const action in changesByTypeAndAction[type]) {
      const changes = changesByTypeAndAction[type][action];
      const lines = preprocessLinesByTypeAndAction(changes, type, action);
      preprocessedLines = preprocessedLines.concat(lines);
    }
  }

  return preprocessedLines;
}

