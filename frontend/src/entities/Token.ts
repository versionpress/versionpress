/// <reference path='../components/search/Search.d.ts' />

import { action, computed, observable } from 'mobx';
import { find } from '../utils/ArrayUtils';
import { trim } from '../utils/SearchUtils';

import Adapter from '../components/search/modifiers/Adapter';
import ListAdapter from '../components/search/modifiers/list/adapter';
import DateAdapter from '../components/search/modifiers/date/adapter';

class Token {

  @observable configHash: SearchConfig;
  @observable key: string;
  @observable modifier: string = null;
  @observable value: string;
  @observable type: string;
  @observable negative: boolean = false;

  constructor(text: string, config: SearchConfig, id: number) {
    this.key = 'token-' + id;
    this.configHash = config;

    if (!text || text === ' ') {
      this.value = ' ';
      this.type = 'space';
      return;
    }

    if (text[0] === '-') {
      text = text.substr(1);
      this.negative = true;
    }

    const modifier = find(Object.keys(config), key => text.substr(0, key.length) === key);
    if (modifier) {
      this.modifier = modifier;
      this.value = text.substr(this.modifier.length);
      this.type = config[this.modifier].type;
    } else {
      this.value = text;
      this.type = config['_default'].type;
    }
  }

  @computed get config() {
    if (this.modifier) {
      return this.configHash[this.modifier];
    }
    return this.configHash['_default'];
  }

  @computed get length() {
    return this.modifier.length + this.value.length + (this.negative ? 1 : 0);
  }

  @computed get adapter(): Adapter {
    if (this.type === 'date') {
      return DateAdapter(this.config);
    }
    return ListAdapter(this.config);
  }

  autoComplete() {
    const value = trim(this.value, true);
    const hints = this.adapter.getHints(this);
    const hint = hints[0];
    const hintValue = trim(this.adapter.serialize(hint));

    if (value.length && hintValue && hintValue.indexOf(value) === 0) {
      return hint;
    }

    return null;
  }

}

export default Token;
