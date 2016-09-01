/// <reference path='../Search.d.ts' />

import * as React from 'react';

interface TokenProps {
  token: Token;
}

const Token: React.StatelessComponent<TokenProps> = ({ token }) => {
  if (isExcluded(token)) {
    if (isValueValid(token)) {
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

function isValueValid(token) {
  return true;
}

export default Token;
