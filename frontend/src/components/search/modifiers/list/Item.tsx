/// <reference path='../../Search.d.ts' />
/// <reference path='./List.d.ts' />

import * as React from 'react';

import ItemHeader from './ItemHeader';
import ItemList from './ItemList';

interface ItemProps {
  currentIndex: number;
  item: GroupedItem;
  onSelectItem(index: number): void;
}

const Item: React.StatelessComponent<ItemProps> = (props) => {
  const { currentIndex, item, onSelectItem } = props;

  return (
    <div>
      <ItemHeader
        item={item}
      />

      {item.list &&
        <ItemList
          currentIndex={currentIndex}
          list={item.list}
          onSelectItem={onSelectItem}
        />
      }
    </div>
  );
};

export default Item;
