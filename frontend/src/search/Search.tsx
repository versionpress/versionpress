/// <reference path='./Search.d.ts' />

import * as React from 'react';

import Input from './input/Input';
import Background from './background/Background';
import {tokenize, prepareConfig} from './utils/';

import './Search.less';

interface SearchProps {
  config: SearchConfig;
}

interface SearchState {
  inputValue: string;
}

export default class Search extends React.Component<SearchProps, SearchState> {

  state = {
    inputValue: '',
  };

  inputNode: HTMLInputElement = null;
  backgroundNode: HTMLDivElement = null;

  onKeyUp = (e: React.KeyboardEvent) => {
    const target = e.target as HTMLInputElement;

    if (target.value !== this.state.inputValue) {
      this.setState({
        inputValue: target.value,
      });
    }
  }

  getTokens() {
    const { config } = this.props;
    const { inputValue } = this.state;

    const tokenConfig = prepareConfig(config);

    return tokenize(inputValue, tokenConfig);
  }

  render() {
    const tokens = this.getTokens();
    const hint = null;

    return (
      <div className='Search'>
        <Input
          ref={node => this.inputNode = node}
          onKeyUp={this.onKeyUp}
        />
        <Background
          ref={node => this.backgroundNode = node}
          tokens={tokens}
          hint={hint}
        />
      </div>
    );
  }

}
