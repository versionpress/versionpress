/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import * as moment from 'moment';

import EntityNameDuplicates from './EntityNameDuplicates';
import Environment from './Environment';
import OverviewLine from './OverviewLine';
import VersionPressLine from './VersionPressLine';
import WordPressUpdateLine from './WordPressUpdateLine';
import * as ArrayUtils from '../../common/ArrayUtils';
import * as StringUtils from '../../common/StringUtils';

interface CommitOverviewProps {
  commit: Commit;
}

interface CommitOverviewState {
  expandedLists: string[];
}

export default class CommitOverview extends React.Component<CommitOverviewProps, CommitOverviewState> {

  state = {
    expandedLists: [],
  };

  onShowMoreClick = (e: React.MouseEvent, listKey: string) => {
    e.preventDefault();
    const { expandedLists } = this.state;

    this.setState({
      expandedLists: expandedLists.concat([listKey]),
    });
  };

  private getFormattedChanges(changes: Change[]) {
    const { commit } = this.props;

    if (changes.length === 0) {
      if (commit.isMerge) {
        return [<em>This is a merge commit. No files were changed in this commit.</em>];
      }
      return [<em>No files were changed in this commit.</em>];
    }

    let displayedLines = [];
    let changesByTypeAndAction = ArrayUtils.groupBy(changes, change => [change.type, change.action]);

    for (let type in changesByTypeAndAction) {
      for (let action in changesByTypeAndAction[type]) {
        let lines: any[];

        if (type === 'usermeta') {
          lines = this.getLinesForUsermeta(changesByTypeAndAction[type][action], action);
        } else if (type === 'postmeta') {
          lines = this.getLinesForPostmeta(changesByTypeAndAction[type][action], action);
        } else if (type === 'versionpress' && (action === 'undo' || action === 'rollback')) {
          lines = this.getLinesForRevert(changesByTypeAndAction[type][action], action);
        } else if (type === 'versionpress' && (action === 'activate' || action === 'deactivate')) {
          lines = this.getLinesForVersionPress(changesByTypeAndAction[type][action], action);
        } else if (type === 'wordpress' && action === 'update') {
          lines = this.getLinesForWordPressUpdate(changesByTypeAndAction[type][action]);
        } else if (type === 'comment') {
          lines = this.getLinesForComments(changesByTypeAndAction[type][action], action);
        } else if (type === 'post') {
          lines = this.getLinesForPosts(changesByTypeAndAction[type][action], action);
        } else {
          lines = this.getLinesForOtherChanges(changesByTypeAndAction[type][action], type, action);
        }

        displayedLines = displayedLines.concat(lines);
      }
    }

    return displayedLines;
  }

  private getLinesForUsermeta(changedMeta: Change[], action: string) {
    return this.getLinesForMeta('usermeta', 'user', 'VP-User-Login', changedMeta, action);
  }

  private getLinesForPostmeta(changedMeta: Change[], action: string) {
    return this.getLinesForMeta('postmeta', 'post', 'VP-Post-Title', changedMeta, action);
  }

  private getLinesForComments(changedComments: Change[], action: string) {
    let lines = [];
    let commentsByPosts = ArrayUtils.groupBy<Change>(changedComments, c => c.tags['VP-Comment-PostTitle']);

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

  private getLinesForPosts(changedPosts: Change[], action: string) {
    const changedPostsByType = ArrayUtils.groupBy(changedPosts, post => post.tags['VP-Post-Type']);
    let lines = [];

    for (const postType in changedPostsByType) {
      const changedEntities = this.renderEntityNamesWithDuplicates(changedPostsByType[postType]);
      let suffix = null;

      if (action === 'trash' || action === 'untrash') {
        suffix = action === 'trash' ? ' to trash' : ' from trash';
        action = 'move';
      }

      lines.push(
        this.renderOverviewLine(postType, action, changedEntities, suffix)
      );
    }

    return lines;
  }

  private getLinesForMeta(entityName: string, parentEntity: string, groupByTag: string, changedMeta: Change[], action: string) {
    const metaByTag = ArrayUtils.groupBy(changedMeta, c => c.tags[groupByTag]);
    let lines = [];

    for (const tagValue in metaByTag) {
      const changedEntities = this.renderEntityNamesWithDuplicates(metaByTag[tagValue]);
      const lineSuffix = [
        ' for ',
        <span className='type'>{parentEntity}</span>,
        ' ',
        <span className='identifier'>{tagValue}</span>,
      ];

      lines.push(
        this.renderOverviewLine(entityName, action, changedEntities, lineSuffix)
      );
    }

    return lines;
  }

  private getLinesForRevert(changes: Change[], action: string) {
    if (action === 'rollback') {
      const commitDetails = changes[0].tags['VP-Commit-Details']; // Rollback is always only 1 change.
      return [`The state is same as it was in "${commitDetails['message']}"`];
    } else {
      return changes.map((change: Change) => {
        const commitDetails = change.tags['VP-Commit-Details'];
        const date = change.tags['VP-Commit-Details']['date'];
        return `Reverted change "${commitDetails['message']}" was made ${moment(date).fromNow()} (${moment(date).format('LLL')})`;
      });
    }
  }

  private getLinesForVersionPress(changes: Change[], action: string) {
    return [
      <VersionPressLine action={action} />,
    ];
  }

  private getLinesForWordPressUpdate(changes: Change[]) {
    return [
      <WordPressUpdateLine version={changes[0].name} />,
    ];
  }

  private getLinesForOtherChanges(changes: Change[], type: string, action: string) {
    const changedEntities = this.renderEntityNamesWithDuplicates(changes);

    return [
      this.renderOverviewLine(type, action, changedEntities),
    ];
  }

  private renderOverviewLine(type: string, action: string, entities: any[], suffix: any = null) {
    const { expandedLists } = this.state;

    return (
      <OverviewLine
        expandedLists={expandedLists}
        type={type}
        action={action}
        entities={entities}
        suffix={suffix}
      />
    );
  }

  private renderEntityNamesWithDuplicates(changes: Change[]) {
    const filteredChanges = ArrayUtils.filterDuplicates<Change>(
      changes,
      change => change.type + '|||' + change.action + '|||' + change.name
    );
    const countOfDuplicates = ArrayUtils.countDuplicates<Change>(
      changes,
      change => [change.type, change.action, change.name]
    );

    return filteredChanges.map((change: Change) => (
      <EntityNameDuplicates
        change={change}
        countOfDuplicates={countOfDuplicates}
        key={change.name}
      />
    ));
  }

  render() {
    const { commit } = this.props;

    return (
      <ul className='overview-list'>
        {this.getFormattedChanges(commit.changes).map((line, i) => <li key={i}>{line}</li>)}
        <Environment environment={commit.environment} />
      </ul>
    );
  }

}
