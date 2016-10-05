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
  popupComponentNode: ModifierComponent = null;

  componentDidUpdate = () => {
    this.scrollBackground();
  };

  onBlur = () => {
    this.setCursorLocation(-1);
  };

  onClick = (e: React.MouseEvent) => {
    const target = e.target as HTMLInputElement;

    this.setCursorLocation(target.selectionStart);
  };

  onCut = (e: React.ClipboardEvent) => {
    const target = e.target as HTMLInputElement;

    this.setCursorLocation(target.selectionStart);
    this.setState({
      inputValue: target.value,
    });
  };

  onPaste = (e: React.ClipboardEvent) => {
    const target = e.target as HTMLInputElement;

    this.setCursorLocation(target.selectionStart);
    this.setState({
      inputValue: target.value,
    });
  };

  onKeyDown = (e: React.KeyboardEvent) => {
    const target = e.target as HTMLInputElement;

    switch (e.keyCode) {
      case KEYS.ENTER:
        if (this.popupComponentNode) {
          e.preventDefault();
          this.popupComponentNode.onSelect();
        }
        break;
      case KEYS.ESC:
        e.preventDefault();
        this.inputNode.blur();
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
      default:
        this.setCursorLocation(target.selectionStart);
        if (target.value !== this.state.inputValue) {
          this.setState({
            inputValue: target.value,
          });
        }
    }
  }

  onKeyUp = (e: React.KeyboardEvent) => {
    const target = e.target as HTMLInputElement;

    this.setCursorLocation(target.selectionStart);
    if (target.value !== this.state.inputValue) {
      this.setState({
        inputValue: target.value,
      });
    }
  };

  onChangeTokenModel = (tokenIndex: number, model: SearchConfigItemContent, shouldMoveCursor: boolean) => {
    const { config } = this.props;
    const tokens = this.getTokens();

    const token = updateToken(tokens[tokenIndex], model, config);
    tokens[tokenIndex] = token;

    const isLastTokenSelected = tokenIndex === this.getActiveTokenIndex();

    let cursorLocation = this.state.cursorLocation;

    if (shouldMoveCursor) {
      if (!model.modifier && isLastTokenSelected) {
        tokens.push(createToken(' '));
      }
      cursorLocation = this.getTokenEndCursorPos(tokens, tokenIndex) + (model.modifier ? 0 : 1);
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
    this.inputNode.value = value;
    this.setState({
      inputValue: value,
    });
  }

  setCursor(location: number) {
    this.setCursorLocation(location);
    setCursor(this.inputNode, location);
  }

  setCursorLocation(location: number) {
    this.scrollBackground();
    this.setState({
      cursorLocation: location,
    });
  }

  scrollInputAndBackground(location: number) {
    this.inputNode.scrollLeft = location;
    this.scrollBackground();
  }

  scrollBackground() {
    if (this.backgroundNode
        && this.backgroundNode.scrollLeft !== this.inputNode.scrollLeft) {
      this.backgroundNode.scrollLeft = this.inputNode.scrollLeft;
    }
  }

  isLastTokenSelected() {
    const tokensCount = this.getTokens().length;
    return tokensCount && (tokensCount - 1) === this.getActiveTokenIndex();
  }

  getTokenEndCursorPos(tokens: Token[], tokenIndex: number) {
    let sum = 0;
    for (var i = 0; i < tokens.length; i++) {
      sum += tokens[i].length;
      if (i === tokenIndex) {
        break;
      }
    }
    return sum;
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

  getTokensString(tokens: Token[]) {
    return tokens
      .reduce((sum, token) => (sum + token.modifier + token.value), '');
  }

  render() {
    const { config } = this.props;

    const tokens = this.getTokens();
    const isLastTokenSelected = this.isLastTokenSelected();
    const activeToken = this.getActiveToken();
    const activeTokenIndex = this.getActiveTokenIndex();

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
        />
        <Background
          nodeRef={node => this.backgroundNode = node}
          tokens={tokens}
          getAdapter={getAdapter(config)}
          isLastTokenSelected={isLastTokenSelected}
          activeToken={activeToken}
        />
        <Popup
          nodeRef={node => this.popupComponentNode = node}
          activeTokenIndex={activeTokenIndex}
          activeToken={activeToken}
          getAdapter={getAdapter(config)}
          onChangeTokenModel={this.onChangeTokenModel}
        />
      </div>
    );
  }

}
