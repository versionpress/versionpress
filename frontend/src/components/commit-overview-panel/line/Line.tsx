/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import UserMeta from './UserMeta';
import PostMeta from './PostMeta';
import Revert from './Revert';
import VersionPress from './VersionPress';
import WordPressUpdate from './WordPressUpdate';
import Comment from './Comment';
import Post from './Post';
import OverviewLine from './OverviewLine';

export interface LineProps {
  changes: Change[];
  expandedLists: string[];
  onShowMoreClick(listKey: string): void;
}

const Line: React.StatelessComponent<LineProps> = (props) => {
  const { changes } = props;
  const { type, action, name } = changes[0];

  if (type === 'usermeta') {
    return <UserMeta {...props} />;
  }
  if (type === 'postmeta') {
    return <PostMeta {...props} />;
  }
  if (type === 'versionpress' && (action === 'undo' || action === 'rollback')) {
    return <Revert {...props} />;
  }
  if (type === 'versionpress' && (action === 'activate' || action === 'deactivate')) {
    return <VersionPress action={action} />;
  }
  if (type === 'wordpress' && action === 'update') {
    return <WordPressUpdate version={name} />;
  }
  if (type === 'comment') {
    return <Comment {...props} />;
  }
  if (type === 'post') {
    return <Post {...props} />;
  }
  return <OverviewLine {...props} />;
};

export default observer(Line);
