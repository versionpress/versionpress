import * as React from 'react';

interface AuthorProps {
  author: Author;
}

const getTitle = (author: Author) => {
  if (author.name === 'Non-admin action') {
    return 'This action is not associated with any user, e.g., it was a public comment';
  }
  if (author.name === 'WP-CLI') {
    return 'This action was done via WP-CLI';
  }
  return `${author.name} <${author.email}>`;
};

const Author: React.StatelessComponent<AuthorProps> = ({ author }) => (
  <td className='column-author'>
    <img
      className='avatar'
      src={author.avatar}
      title={getTitle(author)}
      width={20}
      height={20}
    />
  </td>
);

export default Author;
