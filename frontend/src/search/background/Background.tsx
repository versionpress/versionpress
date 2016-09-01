/// <reference path='../Search.d.ts' />

import * as React from 'react';

import Token from './Token';
import Hint from './Hint';

import './Background.less';

interface BackgroundProps {
  ref?: React.Ref<HTMLDivElement>;
  tokens: Token[];
  hint: any;
}

const Background: React.StatelessComponent<BackgroundProps> = (props) => {
  const {
    ref = null,
    tokens,
    hint,
  } = props;

  return (
    <div className='Search-Background' ref={ref}>
      {tokens.map(token => <Token key={token.key} token={token} />)}
      <Hint hint={hint} />
    </div>
  );

};

export default Background;
