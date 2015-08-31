/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import ArrayUtils = require('../common/ArrayUtils');
import StringUtils = require('../common/StringUtils');

const DOM = React.DOM;

interface CommitOverviewProps {
  commit: Commit;
}

class CommitOverview extends React.Component<CommitOverviewProps, {}> {

  private static formatChanges(changes: Change[]) {
    let displayedLines = [];
    let changesByTypeAndAction = ArrayUtils.groupBy(
      ArrayUtils.filterDuplicates(changes, change => change.type + '|||' + change.action + '|||' + change.name),
        change => [change.type, change.action]
    );

    let countOfDuplicates = ArrayUtils.countDuplicates(changes, change => [change.type, change.action, change.name]);

    for (let type in changesByTypeAndAction) {
      for (let action in changesByTypeAndAction[type]) {
        let lines: any[];

        if (type === 'usermeta') {
          lines = CommitOverview.getLinesForUsermeta(changesByTypeAndAction[type][action], countOfDuplicates, action);
        } else if (type === 'postmeta') {
          lines = CommitOverview.getLinesForPostmeta(changesByTypeAndAction[type][action], countOfDuplicates, action);
        } else if (type === 'versionpress' && (action === 'undo' || action === 'rollback')) {
          lines = CommitOverview.getLinesForRevert(changesByTypeAndAction[type][action], action);
        } else {
          lines = CommitOverview.getLinesForOtherChanges(changesByTypeAndAction[type][action], countOfDuplicates, type, action);
        }

        displayedLines = displayedLines.concat(lines);
      }
    }

    return displayedLines;
  }

  private static renderEntityNamesWithDuplicates(changes: Change[], countOfDuplicates): React.DOMElement<React.HTMLAttributes>[] {
    return changes.map((change: Change) => {
      let duplicatesOfChange = countOfDuplicates[change.type][change.action][change.name];
      let duplicatesSuffix = duplicatesOfChange > 1 ? (' (' + duplicatesOfChange + '×)') : '';
      return DOM.span(null,
        DOM.span(null, CommitOverview.getUserFriendlyName(change)),
        duplicatesSuffix
      );
    });
  }

  private static getLinesForUsermeta(changedMeta: Change[], countOfDuplicates, action: string) {
    return CommitOverview.getLinesForMeta('usermeta', 'user', 'VP-User-Login', changedMeta, countOfDuplicates, action);
  }

  private static getLinesForPostmeta(changedMeta: Change[], countOfDuplicates, action: string) {
    return CommitOverview.getLinesForMeta('postmeta', 'post', 'VP-Post-Id', changedMeta, countOfDuplicates, action);
  }

  private static getLinesForMeta(entityName, parentEntity, groupByTag, changedMeta: Change[], countOfDuplicates, action: string) {
    let lines = [];
    let metaByTag = ArrayUtils.groupBy(changedMeta, c => c.tags[groupByTag]);

    for (let tagValue in metaByTag) {
      let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(metaByTag[tagValue], countOfDuplicates);

      let lineSuffix = [' for ', DOM.span({className: 'type'}, parentEntity), ' ', tagValue];
      let line = CommitOverview.renderOverviewLine(entityName, action, changedEntities, lineSuffix);
      lines.push(line);
    }

    return lines;
  }

  private static getLinesForRevert(changes: Change[], action) {
    let change = changes[0]; // Both undo and rollback are always only 1 change.
    let commitDetails = change.tags['VP-Commit-Details'];
    if (action === 'undo') {
      let date = commitDetails['date'];
      return [`Reverted change was made ${moment(date).fromNow()} (${moment(date).format('LLL')})`];
    } else {
      return [`The state is same as it was in "${commitDetails['message']}"`];
    }
  }

  private static getLinesForOtherChanges(changes, countOfDuplicates, type, action) {
    let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(changes, countOfDuplicates);
    let line = CommitOverview.renderOverviewLine(type, action, changedEntities);
    return [line];
  }

  private static renderOverviewLine(type: string, action: string, entities: string[]|React.DOMElement<any>[], suffix: any = null) {
    let capitalizedVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));

    return DOM.span(null,
      capitalizedVerb,
      ' ',
      DOM.span({className: 'type'}, type),
      ' ',
      ArrayUtils.interspace(entities, ', '),
      suffix
    );
  }

  private static getUserFriendlyName(change: Change) {
    if (change.type === 'user') {
      return change.tags['VP-User-Login'];
    }

    if (change.type === 'usermeta') {
      return change.tags['VP-UserMeta-Key'];
    }

    if (change.type === 'postmeta') {
      return change.tags['VP-PostMeta-Key'];
    }

    if (change.type === 'post') {
      return change.tags['VP-Post-Title'];
    }

    return change.name;
  }

  render() {
    return DOM.ul({className: 'overview-list'}, CommitOverview.formatChanges(this.props.commit.changes).map(line => DOM.li(null, line)));
  }
}

module CommitOverview {
  export interface Props extends CommitOverviewProps {}
}

export = CommitOverview;
