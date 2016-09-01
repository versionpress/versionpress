/// <reference path='./Search.d.ts' />

import * as React from 'react';

import Input from './input/Input';
import Background from './background/Background';
import {tokenize, prepareConfig} from './utils/';
import getAdapter from './modifiers/getAdapter';

import './Search.less';

interface SearchProps {
  config: SearchConfig;
}

interface SearchState {
  inputValue?: string;
  cursorLocation?: number;
}

export default class Search extends React.Component<SearchProps, SearchState> {

  state = {
    inputValue: '',
    cursorLocation: -1,
  };

  inputNode: HTMLInputElement = null;
  backgroundNode: HTMLDivElement = null;

  onBlur = () => {
    this.setState({
      cursorLocation: -1,
    });
  };

  onClick = (e: React.MouseEvent) => {
    const target = e.target as HTMLInputElement;
    this.setState({
      cursorLocation: target.selectionStart,
    });
  };

  onCut = (e: React.ClipboardEvent) => {
    const target = e.target as HTMLInputElement;
    this.setState({
      cursorLocation: target.selectionStart,
      inputValue: target.value,
    });
  };

  onPaste = (e: React.ClipboardEvent) => {
    const target = e.target as HTMLInputElement;
    this.setState({
      cursorLocation: target.selectionStart,
      inputValue: target.value,
    });
  };

  onKeyUp = (e: React.KeyboardEvent) => {
    const target = e.target as HTMLInputElement;

    if (target.value !== this.state.inputValue) {
      this.setState({
        inputValue: target.value,
      });
    }

    this.setState({
      cursorLocation: target.selectionStart,
    });
  };

  isLastTokenSelected() {
    const tokensCount = this.getTokens().length;
    return tokensCount && (tokensCount - 1) === this.getActiveTokenIndex();
  }

  getActiveTokenIndex() {
    const { cursorLocation } = this.state;
    const tokens = this.getTokens();

    let token: Token;
    let prev = 0;
    let start: number;
    let end: number;

    for (var i = 0; i < tokens.length; i++) {
      token = tokens[i];

      start = prev;
      end = token.length + start;
      prev = end;

      if (start < cursorLocation && cursorLocation <= end) {
        return i;
      }
    }
    return -1;
  }

  getActiveToken() {
    const tokens = this.getTokens();
    return tokens[this.getActiveTokenIndex()];
  }

  getTokens() {
    const { config } = this.props;
    const { inputValue } = this.state;

    const tokenConfig = prepareConfig(config);

    return tokenize(inputValue, tokenConfig);
  }

  render() {
    const { config } = this.props;
    const tokens = this.getTokens();
    const isLastTokenSelected = this.isLastTokenSelected();
    const activeToken = this.getActiveToken();

    return (
      <div className='Search'>
        <Input
          ref={node => this.inputNode = node}
          onBlur={this.onBlur}
          onClick={this.onClick}
          onCut={this.onCut}
          onPaste={this.onPaste}
          onKeyUp={this.onKeyUp}
        />
        <Background
          ref={node => this.backgroundNode = node}
          tokens={tokens}
          getAdapter={getAdapter(config)}
          isLastTokenSelected={isLastTokenSelected}
          activeToken={activeToken}
        />
      </div>
    );
  }

}
