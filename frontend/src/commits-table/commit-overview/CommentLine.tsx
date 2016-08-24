import * as React from 'react';

import * as ArrayUtils from '../../common/ArrayUtils';
import * as StringUtils from '../../common/StringUtils';

interface CommentLineProps {
  commentsByPosts: any;
  postTitle: string;
  action: string;
}

const CommentLine: React.StatelessComponent<CommentLineProps> = ({ commentsByPosts, postTitle, action }) => {
  const numberOfComments = commentsByPosts[postTitle].length;
  const authors = ArrayUtils.filterDuplicates(commentsByPosts[postTitle].map((change: Change) => (
    change.tags['VP-Comment-Author'])
  ));
  const authorsString = StringUtils.join(authors);
  let capitalizedVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));
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

  return (
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
};

export default CommentLine;
