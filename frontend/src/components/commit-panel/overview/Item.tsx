import * as React from 'react';

interface ItemProps {
  actionShortcut: string;
  info: string;
}

const getActionVerb = (actionShortcut: string) => {
  if (actionShortcut === 'M') {
    return 'Modified';
  } else if (actionShortcut === '??' || actionShortcut === 'A' || actionShortcut === 'AM') {
    return 'Added';
  } else if (actionShortcut === 'D') {
    return 'Deleted';
  }
};

const Item: React.StatelessComponent<ItemProps> = ({ actionShortcut, info }) => {
  return (
    <li>
      <strong>{getActionVerb(actionShortcut)}</strong>
      <span>{info}</span>
    </li>
  );
};

export default Item;
