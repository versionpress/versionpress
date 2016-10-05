/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import * as ArrayUtils from '../../../utils/ArrayUtils';
import * as StringUtils from '../../../utils/StringUtils';

import { LineProps } from './Line';

const Comment: React.StatelessComponent<LineProps> = ({ changes }) => {
  const action = changes[0].action;
  const postTitle = changes[0].tags['VP-Comment-PostTitle'];

  const count = changes.length;
  const authors = ArrayUtils.filterDuplicates(changes.map((change: Change) => (
    change.tags['VP-Comment-Author'])
  ));

  const authorsString = StringUtils.join(authors);
  const actionVerb = getActionVerb(action);
  const suffix = getSuffix(action);

  return (
    <span>
      {actionVerb}
      {' '}
      {count === 1 ? '' : (count + ' ')}
      <span className='type'>{count === 1 ? 'comment' : 'comments'}</span>
      {' '} by <span className='type'>user</span> <span className='identifier'>{authorsString}</span>
      {' '} for <span className='type'>post</span> <span className='identifier'>{postTitle}</span>
      {suffix}
    </span>
  );
};

function getActionVerb(action: string) {
  if (action === 'spam' || action === 'unspam') {
    return 'Marked';
  }
  if (action === 'trash' || action === 'untrash') {
    return 'Moved';
  }
  if (action === 'create-pending') {
    return 'Created';
  }
  return StringUtils.capitalize(StringUtils.verbToPastTense(action));
}

function getSuffix(action: string) {
  if (action === 'spam') {
    return ' as spam';
  }
  if (action === 'unspam') {
    return ' as not spam';
  }
  if (action === 'trash') {
    return ' to trash';
  }
  if (action === 'untrash') {
    return ' from trash';
  }
  return '';
}

export default observer(Comment);
