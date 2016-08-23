import * as React from 'react';

interface EntityNameDuplicatesProps {
  change: Change;
  countOfDuplicates: any;
}

const getUserFriendlyName = (change: Change) => {
  const { type, name, tags } = change;

  switch (type) {
    case 'user':
      return tags['VP-User-Login'];

    case 'usermeta':
      return tags['VP-UserMeta-Key'];

    case 'postmeta':
      return tags['VP-PostMeta-Key'];

    case 'commentmeta':
      return tags['VP-CommentMeta-Key'];

    case 'post':
      return tags['VP-Post-Title'];

    case 'term':
      return tags['VP-Term-Name'];

    default:
      return name;
  }
};

const EntityNameDuplicates: React.StatelessComponent<EntityNameDuplicatesProps> = ({ change, countOfDuplicates }) => {
  const duplicatesOfChange = countOfDuplicates[change.type][change.action][change.name];

  return (
    <span>
      <span className='identifier'>
        {getUserFriendlyName(change)}
      </span>
      {duplicatesOfChange > 1
        ? ` (${duplicatesOfChange}×)`
        : ''
      }
    </span>
  );
};

export default EntityNameDuplicates;
