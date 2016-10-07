/// <reference path='../../Search.d.ts' />
/// <reference path='./List.d.ts' />

import * as React from 'react';

interface ItemListItemProps {
  currentIndex: number;
  item: SearchConfigItemContent;
  onSelectItem(index: number): void;
}

const ItemListItem: React.StatelessComponent<ItemListItemProps> = (props) => {
  const { item, currentIndex, onSelectItem } = props;
  const className = currentIndex === item.index ? 'is-current' : '';

  return (
    <li
      onMouseDown={(e: React.MouseEvent) => { e.preventDefault(); onSelectItem(item.index); }}
      className={className}
    >
      <span>
        <b>{item.value}</b>
        <span className='Search-modifier-value'>{truncate(item.value.length, item.label)}</span>
      </span>
    </li>
  );
};

function truncate(prefixLength: number, str: string) {
  const maxLength = 50;
  if (prefixLength + str.length > maxLength) {
    const length = prefixLength > maxLength - 2
      ? 0
      : maxLength - prefixLength - 2;
    return str.substring(0, length) + '…';
  }
  return str;
}

export default ItemListItem;
