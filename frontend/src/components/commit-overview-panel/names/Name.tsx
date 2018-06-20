/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

interface NameProps {
  change: Change;
  countOfDuplicates: any;
}

const Name: React.StatelessComponent<NameProps> = ({ change, countOfDuplicates }) => {
  const { type, action, name } = change;

  const count = countOfDuplicates[type][action][name];

  return (
    <span>
      <span className='identifier'>
        {getUserFriendlyName(change)}
      </span>
      {count > 1 &&
        ` (${count}×)`
      }
    </span>
  );
};

function getUserFriendlyName(change: Change) {
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
}

export default observer(Name);
