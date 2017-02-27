/// <reference path='../../Search.d.ts' />

import * as moment from 'moment';

import Adapter from '../Adapter';
import Token from '../../../../entities/Token';
import { getMatch } from '../../../../utils/SearchUtils';

const DATE_FORMAT = 'YYYY-MM-DD';
const DATE_FORMAT_REGEXP = /^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/;

const DateAdapter = (config: SearchConfigItem): Adapter => ({

  getDefaultHint: function() {
    return moment().format(DATE_FORMAT);
  },

  getHints: function(token: Token) {
    if (token) {
      const possibleValues = [moment().format(DATE_FORMAT)];
      return getMatch(token.value, possibleValues);
    }
    return [];
  },

  isValueValid: function(value: string) {
    return DATE_FORMAT_REGEXP.test(value) && moment(value, DATE_FORMAT).isValid();
  },

  serialize: function(date: any) {
    if (!date) {
      return '';
    }
    if (typeof date === 'string') {
      if (this.isValueValid(new Date(date))) {
        return moment(date).format(DATE_FORMAT);
      }
      return date;
    }
    return (date as moment.Moment).format(DATE_FORMAT);
  },

  deserialize: function(value: string) {
    return null;
  },

});

export default DateAdapter;
