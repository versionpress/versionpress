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
      {item.modifier
        ? <span>
            <b>{item.value}</b>
            <span className='modifier-value'>{item.label}</span>
          </span>
        : <span>
            <b>{item.label}</b>
            <span className='modifier-value'>{item.value}</span>
          </span>
      }
    </li>
  );
};

export default ItemListItem;
