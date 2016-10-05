/// <reference path='../../Search.d.ts' />
/// <reference path='./List.d.ts' />

import * as React from 'react';

interface ItemHeaderProps {
  item: GroupedItem;
  token: Token;
}

const ItemHeader: React.StatelessComponent<ItemHeaderProps> = ({ item, token }) => {
  const sectionTitle = item.section || token.sectionTitle;

  if (!sectionTitle) {
    return <div />;
  }

  return (
    <div className='Search-hintMenu-header'>
      <hr />
      <span className='Search-hintMenu-header-label'>
        {sectionTitle}
      </span>
    </div>
  );
};

export default ItemHeader;
