/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import OverviewLine from './OverviewLine';
import { LineProps } from './Line';

const Post: React.StatelessComponent<LineProps> = (props) => {
  const {
    changes,
    expandedLists,
    onShowMoreClick,
  } = props;

  let { action } = changes[0];
  let suffix = null;

  if (action === 'trash' || action === 'untrash') {
    suffix = action === 'trash'
      ? ' to trash'
      : ' from trash';
    action = 'move';
  }

  return (
    <OverviewLine
      expandedLists={expandedLists}
      changes={changes}
      action={action}
      suffix={suffix}
      onShowMoreClick={onShowMoreClick}
    />
  );
};

export default observer(Post);
