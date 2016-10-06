/// <reference path='../../Search.d.ts' />
/// <reference path='../Adapter.d.ts' />

import * as moment from 'moment';

import { getMatch } from '../../utils/';
import * as ArrayUtils from '../../../common/ArrayUtils';

const DateAdapter = (config: SearchConfigItem): Adapter => ({

  autoComplete: function(token: Token) {
    return null;
  },

  getDefaultHint: function() {
    return null;
  },

  getHints: function(token: Token) {
    return null;
  },

  isValueValid: function(value: string) {
    return null;
  },

  serialize: function(item: SearchConfigItemContent) {
    return null;
  },

  deserialize: function(value: string) {
    return null;
  },

});

export default DateAdapter;
