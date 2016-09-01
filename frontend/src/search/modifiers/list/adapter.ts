/// <reference path='../../Search.d.ts' />
/// <reference path='../Adapter.d.ts' />

import * as ArrayUtils from '../../../common/ArrayUtils';

const ListAdapter = (config: SearchConfigItem): Adapter => ({

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
