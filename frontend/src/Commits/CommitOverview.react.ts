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

  static formatChanges(changes: Change[]) {
    let displayedLines = [];
    let changesByTypeAndAction = ArrayUtils.groupBy(
      ArrayUtils.filterDuplicates(changes, change => change.type + '|||' + change.action + '|||' + change.name),
        change => [change.type, change.action]
    );

    let countOfDuplicates = ArrayUtils.countDuplicates(changes, change => [change.type, change.action, change.name]);

    for (let type in changesByTypeAndAction) {
      for (let action in changesByTypeAndAction[type]) {
        if (type === 'usermeta') {
          let lines = CommitOverview.getLinesForUsermeta(changesByTypeAndAction[type][action], countOfDuplicates, action);
          lines.forEach(line => displayedLines.push(line));
        } else {
          let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(changesByTypeAndAction[type][action], countOfDuplicates);
          let line = CommitOverview.renderOverviewLine(type, action, changedEntities);
          displayedLines.push(line);
        }
      }
    }

    return displayedLines;
  }

  static renderEntityNamesWithDuplicates(changes: Change[], countOfDuplicates): React.DOMElement<React.HTMLAttributes>[] {
    return changes.map((change: Change) => {
      let duplicatesOfChange = countOfDuplicates[change.type][change.action][change.name];
      let duplicatesSuffix = duplicatesOfChange > 1 ? (' (' + duplicatesOfChange + '×)') : '';
      return DOM.span(null,
        DOM.span(null, CommitOverview.getUserFriendlyName(change)),
        duplicatesSuffix
      );
    });
  }

  static getLinesForUsermeta(changedMeta: Change[], countOfDuplicates, action: string) {
    let lines = [];
    let metaByUser = ArrayUtils.groupBy(changedMeta, c => c.tags['VP-User-Login']);

    for (let user in metaByUser) {
      let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(metaByUser[user], countOfDuplicates);

      let lineSuffix = [' for ', DOM.span({className: 'type'}, 'user'), ' ', user];
      let line = CommitOverview.renderOverviewLine('usermeta', action, changedEntities, lineSuffix);
      lines.push(line);
    }

    return lines;
  }

  static renderOverviewLine(type: string, action: string, entities: string[]|React.DOMElement<any>[], suffix: any = null) {
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

  static getUserFriendlyName(change: Change) {
    if (change.type === 'user') {
      return change.tags['VP-User-Login'];
    }

    if (change.type === 'usermeta') {
      return change.tags['VP-UserMeta-Key'];
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
