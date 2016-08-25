import * as React from 'react';

interface AuthorProps {
  author: Author;
}

const Author: React.StatelessComponent<AuthorProps> = ({ author }) => {
  let title;

  if (author.name === 'Non-admin action') {
    title = 'This action is not associated with any user, e.g., it was a public comment';
  } else if (author.name === 'WP-CLI') {
    title = 'This action was done via WP-CLI';
  } else {
    title = `${author.name} <${author.email}>`;
  }

  return (
    <td className='column-author'>
      <img
        className='avatar'
        src={author.avatar}
        title={title}
        width={20}
        height={20}
      />
    </td>
  );
};

export default Author;
