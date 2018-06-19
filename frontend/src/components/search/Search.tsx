/// <reference path='./Search.d.ts' />

import * as React from 'react';

import Background from './background/Background';
import Input from './input/Input';
import Popup from './popup/Popup';
import ModifierComponent from './modifiers/ModifierComponent';
import getAdapter from './modifiers/getAdapter';
import {
  createToken,
  KEYS,
  prepareConfig,
  setCursor,
  tokenize,
  updateToken,
} from './utils/';

import './Search.less';

interface SearchProps {
  config: SearchConfig;
  onChange(query: string): void;
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

  inputNode: HTMLInputElement | null = null;
  backgroundNode: HTMLDivElement | null = null;
  popupComponentNode: ModifierComponent<any> | null = null;

  componentDidUpdate() {
    this.scrollBackground();
  }

  onBlur = (e: React.FocusEvent<HTMLInputElement>) => {
    this.setCursorLocation(-1);
  }

  onClick = (e: React.MouseEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.currentTarget.selectionStart!);
  }

  onCut = (e: React.ClipboardEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.currentTarget.selectionStart!);
    this.setState({
      inputValue: e.currentTarget.value,
    });
  }

  onPaste = (e: React.ClipboardEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.currentTarget.selectionStart!);
    this.setState({
      inputValue: e.currentTarget.value,
    });
  }

  onKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    switch (e.keyCode) {
      case KEYS.ENTER:
        if (this.popupComponentNode) {
          if (this.popupComponentNode.onSelect()) {
            e.preventDefault();
          }
        }
        break;
      case KEYS.ESC:
        e.preventDefault();
        this.inputNode!.blur();
        break;
      case KEYS.UP:
        if (this.popupComponentNode) {
          e.preventDefault();
          this.popupComponentNode.onUpClicked();
        }
        break;
      case KEYS.DOWN:
        if (this.popupComponentNode) {
          e.preventDefault();
          this.popupComponentNode.onDownClicked();
        }
        break;
      case KEYS.TAB:
        e.preventDefault();
        const { config } = this.props;

        const tokens = this.getTokens();
        const activeToken = this.getActiveToken(tokens);
        const activeTokenIndex = this.getActiveTokenIndex(tokens);

        const adapter = getAdapter(config)(activeToken);
        const newValue = adapter.autoComplete(activeToken);

        if (newValue) {
          this.onChangeTokenModel(activeTokenIndex, newValue, true);
        } else if (activeToken.type !== 'date') {
          this.popupComponentNode!.onDownClicked();
        }
        break;
      default:
        this.setCursorLocation(e.currentTarget.selectionStart!);
        if (e.currentTarget.value !== this.state.inputValue) {
          this.setState({
            inputValue: e.currentTarget.value,
          });
        }
    }
  }

  onKeyUp = (e: React.KeyboardEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.currentTarget.selectionStart!);
    if (e.currentTarget.value !== this.state.inputValue) {
      this.setState({
        inputValue: e.currentTarget.value,
      });
    }

    this.props.onChange(e.currentTarget.value);
  }

  onChange = (e: React.FormEvent<HTMLInputElement>) => {
    if (e.currentTarget.value !== this.state.inputValue) {
      this.setState({
        inputValue: e.currentTarget.value,
      });
    }

    this.props.onChange(e.currentTarget.value);
  }

  onChangeTokenModel = (tokenIndex: number, model: any, shouldMoveCursor: boolean) => {
    const { config } = this.props;
    const tokens = this.getTokens();

    let token, index = tokenIndex;

    if (tokenIndex === -1 || tokens[tokenIndex].type === 'space') {
      token = updateToken(createToken(''), model, config);
      index = tokens.length;
      tokens.push(token);
    } else {
      token = updateToken(tokens[tokenIndex], model, config);
    }

    const isLastTokenSelected = index === this.getActiveTokenIndex(tokens) && tokens.length - 1 === index;

    let cursorLocation = this.state.cursorLocation;

    if (shouldMoveCursor) {
      if (!model.modifier && isLastTokenSelected) {
        tokens.push(createToken(' '));
      }
      cursorLocation = this.getTokenEndCursorPos(tokens, index) + (model.modifier ? 0 : 1);
    }

    const tokensString = this.getTokensString(tokens);
    this.setInputValue(tokensString);
    this.setCursor(cursorLocation);

    if (isLastTokenSelected) {
      this.scrollInputAndBackground(Number.MAX_VALUE);
    } else {
      this.scrollBackground();
    }
  }

  setInputValue(value: string) {
    this.inputNode!.value = value;
    this.setState({
      inputValue: value,
    });
  }

  setCursor(location: number) {
    this.setCursorLocation(location);
    setCursor(this.inputNode!, location);
  }

  setCursorLocation(location: number) {
    this.scrollBackground();
    this.setState({
      cursorLocation: location,
    });
  }

  scrollInputAndBackground(location: number) {
    this.inputNode!.scrollLeft = location;
    this.scrollBackground();
  }

  scrollBackground() {
    if (this.backgroundNode
        && this.backgroundNode.scrollLeft !== this.inputNode!.scrollLeft) {
      this.backgroundNode.scrollLeft = this.inputNode!.scrollLeft;
    }
  }

  displayPopup(tokens: Token[]) {
    const { cursorLocation } = this.state;
    const activeToken = this.getActiveToken(tokens);
    const isLastTokenSelected = this.isLastTokenSelected(tokens);

    return cursorLocation !== -1
           && (!this.state.inputValue
               || isLastTokenSelected
               || (activeToken && activeToken.type !== 'space'));
  }

  isLastTokenSelected(tokens: Token[]) {
    const tokensCount = tokens.length;
    return tokensCount && (tokensCount - 1) === this.getActiveTokenIndex(tokens);
  }

  getTokenEndCursorPos(tokens: Token[], tokenIndex: number) {
    let sum = 0;
    for (let i = 0; i < tokens.length; i++) {
      sum += tokens[i].length;
      if (i === tokenIndex) {
        break;
      }
    }
    return sum;
  }

  getActiveTokenIndex(tokens: Token[]) {
    const { cursorLocation } = this.state;

    let token: Token;
    let prev = 0;
    let start: number;
    let end: number;

    for (let i = 0; i < tokens.length; i++) {
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

  getActiveTokenCursor(tokens: Token[]) {
    const { cursorLocation } = this.state;

    const activeTokenIndex = this.getActiveTokenIndex(tokens);
    const activeToken = tokens[activeTokenIndex];

    if (activeTokenIndex > -1) {
      const tokenCursorEnd = this.getTokenEndCursorPos(tokens, activeTokenIndex);
      return cursorLocation - (tokenCursorEnd - activeToken.length);
    }
    return -1;
  }

  getActiveToken(tokens: Token[]) {
    return tokens[this.getActiveTokenIndex(tokens)];
  }

  getTokens() {
    const { config } = this.props;
    const { inputValue } = this.state;

    const tokenConfig = prepareConfig(config);

    return tokenize(inputValue, tokenConfig);
  }

  getTokensString(tokens: Token[]) {
    return tokens
      .reduce((sum, token) => (sum + (token.negative ? '-' : '') + token.modifier + token.value), '');
  }

  render() {
    const { config } = this.props;

    const tokens = this.getTokens();
    const isLastTokenSelected = this.isLastTokenSelected(tokens);
    const activeToken = this.getActiveToken(tokens);
    const activeTokenIndex = this.getActiveTokenIndex(tokens);
    const activeTokenCursor = this.getActiveTokenCursor(tokens);
    const displayPopup = this.displayPopup(tokens);

    return (
      <div className='Search'>
        <Input
          nodeRef={node => this.inputNode = node}
          onBlur={this.onBlur}
          onClick={this.onClick}
          onCut={this.onCut}
          onPaste={this.onPaste}
          onKeyDown={this.onKeyDown}
          onKeyUp={this.onKeyUp}
          onChange={this.onChange}
        />
        <Background
          nodeRef={node => this.backgroundNode = node}
          tokens={tokens}
          getAdapter={getAdapter(config)}
          isLastTokenSelected={!!isLastTokenSelected}
          activeToken={activeToken}
        />
        {displayPopup &&
          <Popup
            nodeRef={node => this.popupComponentNode = node}
            activeTokenIndex={activeTokenIndex}
            cursor={activeTokenCursor}
            token={activeToken}
            adapter={getAdapter(config)(activeToken)}
            onChangeTokenModel={this.onChangeTokenModel}
          />
        }
      </div>
    );
  }

}
