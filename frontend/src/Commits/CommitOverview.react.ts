/// <reference path='../../typings/tsd.d.ts' />
/// <reference path='./Commits.d.ts' />

import React = require('react');
import ArrayUtils = require('../common/ArrayUtils');
import StringUtils = require('../common/StringUtils');

const DOM = React.DOM;

interface CommitOverviewProps {
  commit: Commit;
}

interface CommitOverviewState {
  expandedLists: string[];
}

class CommitOverview extends React.Component<CommitOverviewProps, CommitOverviewState> {

  constructor(props: CommitOverviewProps, context: any) {
    super(props, context);
    this.state = {expandedLists: []};
  }

  private formatChanges(changes: Change[]) {
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
          lines = this.getLinesForUsermeta(changesByTypeAndAction[type][action], countOfDuplicates, action);
        } else if (type === 'postmeta') {
          lines = this.getLinesForPostmeta(changesByTypeAndAction[type][action], countOfDuplicates, action);
        } else if (type === 'versionpress' && (action === 'undo' || action === 'rollback')) {
          lines = this.getLinesForRevert(changesByTypeAndAction[type][action], action);
        } else if (type === 'comment') {
          lines = this.getLinesForComments(changesByTypeAndAction[type][action], action);
        } else if (type === 'post') {
          lines = this.getLinesForPosts(changesByTypeAndAction[type][action], countOfDuplicates, action);
        } else {
          lines = this.getLinesForOtherChanges(changesByTypeAndAction[type][action], countOfDuplicates, type, action);
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

  private getLinesForUsermeta(changedMeta: Change[], countOfDuplicates, action: string) {
    return this.getLinesForMeta('usermeta', 'user', 'VP-User-Login', changedMeta, countOfDuplicates, action);
  }

  private getLinesForPostmeta(changedMeta: Change[], countOfDuplicates, action: string) {
    return this.getLinesForMeta('postmeta', 'post', 'VP-Post-Title', changedMeta, countOfDuplicates, action);
  }

  private getLinesForComments(changedComments: Change[], action: string) {
    let lines = [];
    let commentsByPosts = ArrayUtils.groupBy(changedComments, c => c.tags['VP-Comment-PostTitle']);

    for (let postTitle in commentsByPosts) {
      let capitalizedVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));
      let numberOfComments = commentsByPosts[postTitle].length;
      let authors = ArrayUtils.filterDuplicates(commentsByPosts[postTitle].map(change => change.tags['VP-Comment-Author']));
      let authorsString = StringUtils.join(authors);
      let suffix = '';

      if (action === 'spam' || action === 'unspam') {
        capitalizedVerb = 'Marked';
        suffix = action === 'spam' ? ' as spam' : ' as not spam';
      }

      if (action === 'trash' || action === 'untrash') {
        capitalizedVerb = 'Moved';
        suffix = action === 'trash' ? ' to trash' : ' from trash';
      }

      if (action === 'create-pending') {
        capitalizedVerb = 'Created';
      }

      let line = DOM.span(null,
        capitalizedVerb,
        ' ',
        numberOfComments === 1 ? '' : (numberOfComments + ' '),
        DOM.span({className: 'type'}, numberOfComments === 1 ? 'comment' : 'comments'),
        ' by ',
        DOM.span({className: 'type'}, 'user'),
        ' ',
        authorsString,
        ' for ',
        DOM.span({className: 'type'}, 'post'),
        ' ',
        postTitle,
        suffix
      );
      lines.push(line);
    }

    return lines;
  }

  private getLinesForPosts(changedPosts: Change[], countOfDuplicates, action: string) {
    let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(changedPosts, countOfDuplicates);
    let suffix = null;

    if (action === 'trash' || action === 'untrash') {
      suffix = action === 'trash' ? ' to trash' : ' from trash';
      action = 'move';
    }

    let line = this.renderOverviewLine('post', action, changedEntities, suffix);
    return [line];
  }


  private getLinesForMeta(entityName, parentEntity, groupByTag, changedMeta: Change[], countOfDuplicates, action: string) {
    let lines = [];
    let metaByTag = ArrayUtils.groupBy(changedMeta, c => c.tags[groupByTag]);

    for (let tagValue in metaByTag) {
      let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(metaByTag[tagValue], countOfDuplicates);

      let lineSuffix = [' for ', DOM.span({className: 'type'}, parentEntity), ' ', tagValue];
      let line = this.renderOverviewLine(entityName, action, changedEntities, lineSuffix);
      lines.push(line);
    }

    return lines;
  }

  private getLinesForRevert(changes: Change[], action) {
    let change = changes[0]; // Both undo and rollback are always only 1 change.
    let commitDetails = change.tags['VP-Commit-Details'];
    if (action === 'undo') {
      let date = commitDetails['date'];
      return [`Reverted change was made ${moment(date).fromNow()} (${moment(date).format('LLL')})`];
    } else {
      return [`The state is same as it was in "${commitDetails['message']}"`];
    }
  }

  private getLinesForOtherChanges(changes, countOfDuplicates, type, action) {
    let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(changes, countOfDuplicates);
    let line = this.renderOverviewLine(type, action, changedEntities);
    return [line];
  }

  private renderOverviewLine(type: string, action: string, entities: any[], suffix: any = null) {
    let capitalizedVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));

    if (entities.length < 5) {
      return DOM.span(null,
        capitalizedVerb,
        ' ',
        DOM.span({className: 'type'}, entities.length === 1 ? type : StringUtils.pluralize(type)),
        ' ',
        ArrayUtils.interspace(entities, ', ', ' and '),
        suffix
      );
    }

    let listKey = `${type}|||${action}|||${suffix}`;
    let entityList;
    if (this.state.expandedLists.indexOf(listKey) > -1) {
      entityList = DOM.ul(null,
        entities.map(entity => DOM.li(null, entity)),
        DOM.li(null, DOM.a({onClick: () => this.expandList(listKey)}, 'show less...'))
      );
    } else {
      let displayedListLength = 3;
      entityList = DOM.ul(null,
        entities.slice(0, displayedListLength).map(entity => DOM.li(null, entity)),
        DOM.li(null, DOM.a({onClick: () => this.collapseList(listKey)}, 'show ', entities.length - displayedListLength, ' more...'))
      );
    }

    return DOM.span(null,
      capitalizedVerb,
      ' ',
      DOM.span({className: 'type'}, StringUtils.pluralize(type)),
      ' ',
      suffix,
      entityList
    );
  }

  private expandList(listKey) {
    let expandedLists = this.state.expandedLists;
    let index = expandedLists.indexOf(listKey);
    let newExpandedLists = expandedLists.slice(0, index).concat(expandedLists.slice(index + 1));
    this.setState({expandedLists: newExpandedLists});
  }

  private collapseList(listKey) {
    let expandedLists = this.state.expandedLists;
    let newExpandedLists = expandedLists.concat([listKey]);
    this.setState({expandedLists: newExpandedLists});
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

    if (change.type === 'term') {
      return change.tags['VP-Term-Name'];
    }

    return change.name;
  }

  render() {
    return DOM.ul({className: 'overview-list'}, this.formatChanges(this.props.commit.changes).map(line => DOM.li(null, line)));
  }
}

module CommitOverview {
  export interface Props extends CommitOverviewProps {}
}

export = CommitOverview;
