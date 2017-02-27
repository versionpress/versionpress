/// <reference path='../../Search.d.ts' />

import Adapter from '../Adapter';
import Token from '../../../../entities/Token';
import { getMatch, trim, IN_QUOTES_REGEXP } from '../../../../utils/SearchUtils';
import * as ArrayUtils from '../../../../utils/ArrayUtils';

const ListAdapter = (config: SearchConfigItem): Adapter => ({

  getDefaultHint: function() {
    return config
      ? config.defaultHint
      : '';
  },

  getHints: function(token: Token) {
    const list = config && config.content;

    if (list && list.length) {
      if (token && token.type !== 'space' && (token.modifier.length || token.value.length)) {
        const value = trim(token.value, true);

        const labelMatches = getMatch(value, list, 'label');
        const valueMatches = getMatch(value, list, 'value');

        return labelMatches
          .concat(valueMatches)
          .filter((value, index, self) => self.indexOf(value) === index)
          .filter(item => item.value !== value)
          .sort((a, b) => a.value.length - b.value.length);
      }

      return list
        .filter(item => item.modifier)
        .sort((a, b) => a.value.length - b.value.length);
    }

    return [];
  },

  isValueValid: function(value: string) {
    const list = config && config.content;

    if (list) {
      return list.some(item => trim(this.serialize(item)) === trim(value));
    }
    return !!value;
  },

  serialize: function(item: SearchConfigItemContent) {
    if (!item) {
      return null;
    }
    if (IN_QUOTES_REGEXP.test(item.value) || item.value.indexOf(' ') === -1) {
      return item.value;
    }
    return '"' + item.value + '"';
  },

  deserialize: function(value: string) {
    const list = config && config.content;

    if (list) {
      return ArrayUtils.find(list, item => item.value === value);
    }
    return value;
  },

});

export default ListAdapter;
