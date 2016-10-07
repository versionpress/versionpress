/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

import { trim } from '../utils';

interface HintProps {
  token: Token;
  adapter: Adapter;
}

const Hint: React.StatelessComponent<HintProps> = ({ token, adapter }) => {
  const value = trim(token.value, true);

  const hint = value.length
    ? getSubHint(token, adapter)
    : adapter.getDefaultHint();

  return (
    <span className='Search-Background-hint'>{hint}</span>
  );
};

function getSubHint(token: Token, adapter: Adapter): string {
  const value = trim(token.value, true);
  const hints = adapter.getHints(token);
  const hint = trim(adapter.serialize(hints[0]));

  if (value.length && hint && hint.indexOf(value) === 0) {
    return hint.substr(value.length);
  }
  return '';
}

export default Hint;
