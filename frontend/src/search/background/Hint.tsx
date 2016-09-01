/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

interface HintProps {
  token: Token;
  adapter: Adapter;
}

const Hint: React.StatelessComponent<HintProps> = ({ token, adapter }) => {
  const { value } = token;

  const hint = value.length
    ? getSubHint(token, adapter)
    : adapter.getDefaultHint();

  return (
    <span className='Search-Background-hint'>{hint}</span>
  );
};

function getSubHint(token: Token, adapter: Adapter): string {
  const { value } = token;

  const hints = adapter.getHints(value);
  const hint = adapter.serialize(hints[0]);

  if (value.length && hint && hint.indexOf(value) === 0) {
    return hint.substr(value.length);
  }
  return '';
}

export default Hint;
