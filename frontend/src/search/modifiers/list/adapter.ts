/// <reference path='../../Search.d.ts' />
/// <reference path='../Adapter.d.ts' />

import { getMatch } from '../../utils/';
import * as ArrayUtils from '../../../common/ArrayUtils';

const ListAdapter = (config: SearchConfigItem): Adapter => ({

  getDefaultHint: function() {
    return config
      ? config.defaultHint
      : '';
  },

  getHints: function(value: string) {
    const list = config && config.content;

    if (list && list.length) {
      const labelMatches = getMatch(value, list, 'label');
      const valueMatches = getMatch(value, list, 'value');

      const matches = labelMatches
        .concat(valueMatches)
        .filter((value, index, self) => self.indexOf(value) === index)
        .filter(item => item.value !== value)
        .sort((a, b) => a.value.length - b.value.length );

      if (matches.length) {
        return matches;
      }
    }

    return [];
  },

  isValueValid: function(value: string) {
    const list = config && config.content;

    if (list) {
      return list.some(item => this.serialize(item) === value);
    }
    return !!value;
  },

  serialize: function(item: SearchConfigItemContent) {
    return item && item.value;
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
