/// <reference path='../../typings/typings.d.ts' />
/// <reference path='./Commits.d.ts' />

import * as React from 'react';
import * as moment from 'moment';
import * as ArrayUtils from '../common/ArrayUtils';
import * as StringUtils from '../common/StringUtils';

interface CommitOverviewProps extends React.Props<JSX.Element> {
  commit: Commit;
}

interface CommitOverviewState {
  expandedLists: string[];
}

export default class CommitOverview extends React.Component<CommitOverviewProps, CommitOverviewState> {

  constructor(props: CommitOverviewProps, context: any) {
    super(props, context);
    this.state = {expandedLists: []};
  }

  private static renderEntityNamesWithDuplicates(changes: Change[], countOfDuplicates): JSX.Element[] {
    return changes.map((change: Change) => {
      let duplicatesOfChange = countOfDuplicates[change.type][change.action][change.name];
      let duplicatesSuffix = duplicatesOfChange > 1 ? (' (' + duplicatesOfChange + '×)') : '';
      return (
        <span>
          <span className='identifier'>{CommitOverview.getUserFriendlyName(change)}</span>
          {duplicatesSuffix}
        </span>
      );
    });
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
    return (
      <ul className='overview-list'>
        {this.formatChanges(this.props.commit.changes).map((line, i) => <li key={i}>{line}</li>)}
      </ul>
    );
  }

  private formatChanges(changes: Change[]) {
    if (changes.length === 0) {
      if (this.props.commit.isMerge) {
        return [<em>This is a merge commit. No files were changed in this commit.</em>];
      }
      return [<em>No files were changed in this commit.</em>];
    }

    let displayedLines = [];
    let changesByTypeAndAction = ArrayUtils.groupBy(
      ArrayUtils.filterDuplicates<Change>(changes, change => change.type + '|||' + change.action + '|||' + change.name),
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
        } else if (type === 'versionpress' && (action === 'activate' || action === 'deactivate')) {
          lines = this.getLinesForVersionPress(changesByTypeAndAction[type][action], action);
        } else if (type === 'wordpress' && action === 'update') {
          lines = this.getLinesForWordPressUpdate(changesByTypeAndAction[type][action]);
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

      let line = (
        <span>
          {capitalizedVerb}
          {' '}
          {numberOfComments === 1 ? '' : (numberOfComments + ' ')}
          <span className='type'>{numberOfComments === 1 ? 'comment' : 'comments'}</span>
          {' '} by <span className='type'>user</span> <span className='identifier'>{authorsString}</span>
          {' '} for <span className='type'>post</span> <span className='identifier'>{postTitle}</span>
          {suffix}
        </span>
      );
      lines.push(line);
    }

    return lines;
  }

  private getLinesForPosts(changedPosts: Change[], countOfDuplicates, action: string) {
    let lines = [];
    let changedPostsByType = ArrayUtils.groupBy(changedPosts, post => post.tags['VP-Post-Type']);

    for (let postType in changedPostsByType) {
      let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(changedPostsByType[postType], countOfDuplicates);
      let suffix = null;

      if (action === 'trash' || action === 'untrash') {
        suffix = action === 'trash' ? ' to trash' : ' from trash';
        action = 'move';
      }

      let line = this.renderOverviewLine(postType, action, changedEntities, suffix);
      lines.push(line);
    }

    return lines;
  }

  private getLinesForMeta(entityName, parentEntity, groupByTag, changedMeta: Change[], countOfDuplicates, action: string) {
    let lines = [];
    let metaByTag = ArrayUtils.groupBy(changedMeta, c => c.tags[groupByTag]);

    for (let tagValue in metaByTag) {
      let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(metaByTag[tagValue], countOfDuplicates);

      let lineSuffix = [
        ' for ',
        <span className='type'>{parentEntity}</span>,
        ' ',
        <span className='identifier'>{tagValue}</span>
      ];
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

  private getLinesForVersionPress(changes: Change[], action) {
    let line = (
      <span>
        {StringUtils.capitalize(StringUtils.verbToPastTense(action))}
        {' '}
        <span className='identifier'>VersionPress</span>
      </span>
    );
    return [line];
  }

  private getLinesForWordPressUpdate(changes: Change[]) {
    let change = changes[0];
    let line = (
      <span>
        Updated <span className='identifier'>WordPress</span>
        {' '} to version <span className='identifier'>{change.name}</span>
      </span>
    );
    return [line];
  }

  private getLinesForOtherChanges(changes, countOfDuplicates, type, action) {
    let changedEntities = CommitOverview.renderEntityNamesWithDuplicates(changes, countOfDuplicates);
    let line = this.renderOverviewLine(type, action, changedEntities);
    return [line];
  }

  private renderOverviewLine(type: string, action: string, entities: any[], suffix: any = null) {
    let capitalizedVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));

    if (entities.length < 5) {
      return (
        <span>
          {capitalizedVerb}
          {' '} <span className='type'>{entities.length === 1 ? type : StringUtils.pluralize(type)}</span>
          {' '} {ArrayUtils.interspace(entities, ', ', ' and ')}
          {suffix}
        </span>
      );
    }

    let listKey = `${type}|||${action}|||${suffix}`;
    let entityList;
    if (this.state.expandedLists.indexOf(listKey) > -1) {
      entityList = (
        <ul>
          {entities.map(entity => <li>{entity}</li>)}
        </ul>
      );
    } else {
      let displayedListLength = 3;
      entityList = (
        <ul>
          {entities.slice(0, displayedListLength).map(entity => <li>{entity}</li>)}
          <li>
            <a onClick={() => this.expandList(listKey)}>
              show {entities.length - displayedListLength} more...
            </a>
          </li>
        </ul>
      );
    }

    return (
      <span>
        {capitalizedVerb}
        {' '} <span className='type'>{StringUtils.pluralize(type)}</span>
        {suffix}
        {entityList}
      </span>
    );
  }

  private expandList(listKey) {
    let expandedLists = this.state.expandedLists;
    let newExpandedLists = expandedLists.concat([listKey]);
    this.setState({expandedLists: newExpandedLists});
  }

}
