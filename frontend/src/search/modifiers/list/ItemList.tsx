/// <reference path='../../Search.d.ts' />

import * as React from 'react';

import ItemListItem from './ItemListItem';

interface ItemListProps {
  currentIndex: number;
  list: SearchConfigItemContent[];
  onSelectItem(index: number): void;
}

const ItemList: React.StatelessComponent<ItemListProps> = ({ currentIndex, list, onSelectItem }) => (
  <ul className='Search-inputList'>
    {list.map(item => (
      <ItemListItem
        key={item.index}
        currentIndex={currentIndex}
        item={item}
        onSelectItem={onSelectItem}
      />
    ))}
  </ul>
);

export default ItemList;
