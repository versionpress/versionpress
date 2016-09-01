/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

import Token from './Token';
import Hint from './Hint';

import './Background.less';

interface BackgroundProps {
  ref?: React.Ref<HTMLDivElement>;
  tokens: Token[];
  isLastTokenSelected: boolean;
  activeToken: Token;
  getAdapter(token: Token): Adapter;
}

const Background: React.StatelessComponent<BackgroundProps> = (props) => {
  const {
    ref = null,
    tokens,
    getAdapter,
    isLastTokenSelected,
    activeToken,
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
      {isLastTokenSelected &&
        <Hint
          adapter={getAdapter(activeToken)}
          token={activeToken}
        />
      }
    </div>
  );

};

export default Background;
