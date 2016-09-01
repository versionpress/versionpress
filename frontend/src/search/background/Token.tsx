/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

interface TokenProps {
  token: Token;
  adapter: Adapter;
}

const Token: React.StatelessComponent<TokenProps> = ({ token, adapter }) => {
  if (isExcluded(token)) {
    if (adapter.isValueValid(token.value)) {
      return (
        <span className='Search-Background-modifier'>
          {token.modifier}{token.value}
        </span>
      );
    }
    return (
      <span>
        <span className='Search-Background-modifier is-incomplete'>
          {token.modifier}
        </span>
        {token.value}
      </span>
    );
  }

  return (
    <span>{token.modifier}{token.value}</span>
  );
};

const excludedTokenTypes = ['default', 'modifier-list', 'space'];

function isExcluded(token) {
  return excludedTokenTypes.indexOf(token.type) === -1;
}

export default Token;
