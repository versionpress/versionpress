/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

import Token from './Token';
import Hint from './Hint';

import './Background.less';

interface BackgroundProps {
  ref?: React.Ref<HTMLDivElement>;
  tokens: Token[];
  hint: any;
  getAdapter(token: Token): Adapter;
}

const Background: React.StatelessComponent<BackgroundProps> = (props) => {
  const {
    ref = null,
    tokens,
    getAdapter,
    hint,
  } = props;

  return (
    <div className='Search-Background' ref={ref}>
      {tokens.map(token => (
        <Token
          key={token.key}
          adapter={getAdapter(token)}
          token={token}
        />
      ))}
      <Hint hint={hint} />
    </div>
  );

};

export default Background;
