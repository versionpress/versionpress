/// <reference path='../components/search/Search.d.ts' />

import { action, computed, observable } from 'mobx';

import { tokenize, prepareConfig } from '../utils/SearchUtils';
import Token from '../entities/Token';

class SearchStore {

  @observable config: SearchConfig;
  @observable inputValue: string = '';
  @observable cursorLocation: number = -1;

  @computed get tokens() {
    const tokenConfig = prepareConfig(this.config);
    return tokenize(this.inputValue, tokenConfig);
  }

  @computed get activeToken() {
    return this.tokens[this.activeTokenIndex];
  }

  @computed get isLastTokenSelected() {
    const tokensCount = this.tokens.length;
    return tokensCount && (tokensCount - 1) === this.activeTokenIndex;
  }

  @computed get activeTokenIndex() {
    let token: Token;
    let prev = 0;
    let start: number;
    let end: number;

    for (var i = 0; i < this.tokens.length; i++) {
      token = this.tokens[i];

      start = prev;
      end = token.length + start;
      prev = end;

      if (start < this.cursorLocation && this.cursorLocation <= end) {
        return i;
      }
    }
    return -1;
  }

  @computed get activeTokenCursor() {
    if (this.activeTokenIndex > -1) {
      const tokenCursorEnd = this.tokens
                               .slice(0, this.activeTokenIndex + 1)
                               .reduce((prev, token) => prev + token.length, 0);
      return this.cursorLocation - (tokenCursorEnd - this.activeToken.length);
    }
    return -1;
  }

  @computed isPopupDisplayed() {
    return this.cursorLocation !== -1
           && (!this.inputValue
               || this.isLastTokenSelected
               || (this.activeToken && this.activeToken.type !== 'space'));
  }

  @action setConfig = (config: SearchConfig) => {
    this.config = config;
  };

  @action setInputValue = (value: string) => {
    this.inputValue = value;
  }

  @action setCursorLocation = (cursorLocation: number) => {
    this.cursorLocation = cursorLocation;
  }

}

const searchStore = new SearchStore();

export { SearchStore };
export default searchStore;
