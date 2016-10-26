import * as React from 'react';
import { observer } from 'mobx-react';

interface AuthorProps {
  author: Author;
}

const Author: React.StatelessComponent<AuthorProps> = ({ author }) => (
  <div className='column-author'>
    <img
      className='avatar'
      src={author.avatar}
      title={getTitle(author)}
      width={20}
      height={20}
    />
  </div>
);

function getTitle(author: Author) {
  if (author.name === 'Non-admin action') {
    return 'This action is not associated with any user, e.g., it was a public comment';
  }
  if (author.name === 'WP-CLI') {
    return 'This action was done via WP-CLI';
  }
  return `${author.name} <${author.email}>`;
}

export default observer(Author);
