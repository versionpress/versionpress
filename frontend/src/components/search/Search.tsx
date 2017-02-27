/// <reference path='./Search.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import Background from './background/Background';
import Input from './input/Input';
import Popup from './popup/Popup';
import ModifierComponent from './modifiers/ModifierComponent';
import getAdapter from './modifiers/getAdapter';
import {
  KEYS,
  setCursor,
  updateToken,
} from './utils/';

import Token from '../../entities/Token';
import { SearchStore } from '../../stores/searchStore';

import './Search.less';

interface SearchProps {
  config: SearchConfig;
  searchStore?: SearchStore;
  onChange?(query: string): void;
}

@observer(['searchStore'])
export default class Search extends React.Component<SearchProps, {}> {

  inputNode: HTMLInputElement = null;
  backgroundNode: HTMLDivElement = null;
  popupComponentNode: ModifierComponent<any> = null;

  componentDidUpdate = () => {
    this.scrollBackground();
  };

  onBlur = (e: React.FocusEvent<HTMLInputElement>) => {
    this.setCursorLocation(-1);
  };

  onClick = (e: React.MouseEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.target.selectionStart);
  };

  onClipboardEvent = (e: React.ClipboardEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.target.selectionStart);
    this.props.searchStore.setInputValue(e.target.value);
  };

  onKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    switch (e.keyCode) {
      case KEYS.ENTER:
        if (this.popupComponentNode && this.popupComponentNode.onSelect()) {
          e.preventDefault();
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
      case KEYS.TAB:
        e.preventDefault();
        const { config, searchStore } = this.props;
        const { activeToken } = searchStore;

        const newValue = activeToken.autoComplete();

        if (newValue) {
          this.onChangeTokenModel(searchStore.activeTokenIndex, newValue, true);
        } else if (searchStore.activeToken.type !== 'date') {
          this.popupComponentNode.onDownClicked();
        }
        break;
      default:
        this.setCursorLocation(e.target.selectionStart);
        if (e.target.value !== searchStore.inputValue) {
          searchStore.setInputValue(e.target.value);
        }
    }
  }

  onKeyUp = (e: React.KeyboardEvent<HTMLInputElement>) => {
    this.setCursorLocation(e.target.selectionStart);
    const { searchStore } = this.props;

    if (e.target.value !== searchStore.inputValue) {
      searchStore.setInputValue(e.target.value);
      this.props.onChange(e.target.value);
    }
  };

  onChange = (e: React.FormEvent<HTMLInputElement>) => {
    const { searchStore } = this.props;

    if (e.target.value !== searchStore.inputValue) {
      searchStore.setInputValue(e.target.value);
      this.props.onChange(e.target.value);
    }
  }

  onChangeTokenModel = (tokenIndex: number, model: any, shouldMoveCursor: boolean) => {
    const { config, searchStore } = this.props;
    const { tokens } = searchStore;

    let token, index = tokenIndex;

    if (tokenIndex === -1 || tokens[tokenIndex].type === 'space') {
      token = updateToken(createToken(null), model, config);
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
    this.setCursor(cursorLocation);
    this.inputNode.value = tokensString;
    searchStore.setInputValue(tokensString);

    if (isLastTokenSelected) {
      this.scrollInputAndBackground(Number.MAX_VALUE);
    } else {
      this.scrollBackground();
    }
  }

  setCursor(location: number, updateDom: boolean = false) {
    this.setCursorLocation(location);
    setCursor(this.inputNode, location);
  }

  setCursorLocation(location: number) {
    const { searchStore } = this.props;
    this.scrollBackground();
    searchStore.setCursorLocation(location);
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

  getTokensString(tokens: Token[]) {
    return tokens
      .reduce((sum, token) => (sum + (token.negative ? '-' : '') + token.modifier + token.value), '');
  }

  render() {
    const { config, searchStore } = this.props;
    const {
      activeToken,
      activeTokenIndex,
      activeTokenCursor,
      isLastTokenSelected,
      isPopupDisplayed,
      tokens,
    } = searchStore;

    return (
      <div className='Search'>
        <Input
          nodeRef={node => this.inputNode = node}
          onBlur={this.onBlur}
          onClick={this.onClick}
          onCut={this.onClipboardEvent}
          onPaste={this.onClipboardEvent}
          onKeyDown={this.onKeyDown}
          onKeyUp={this.onKeyUp}
          onChange={this.onChange}
        />
        <Background
          nodeRef={node => this.backgroundNode = node}
          tokens={tokens}
          getAdapter={getAdapter(config)}
          isLastTokenSelected={isLastTokenSelected}
          activeToken={activeToken}
        />
        {isPopupDisplayed &&
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
