/// <reference path='../Search.d.ts' />
/// <reference path='./Adapter.d.ts' />

import DefaultAdapter from './default/adapter';
import ListAdapter from './list/adapter';

export default function getAdapter(config: SearchConfig) {
  return (token: Token): Adapter => {
    const configItem = getConfigItem(token, config);
    const { type } = token;

    if (type === 'list' || type === 'modifier-list') {
      return ListAdapter(configItem);
    }
    /*
    if (type === 'date') {
      return DateAdapter;
    }
    */

    return DefaultAdapter(configItem);
  };
}

function getConfigItem(token: Token, config: SearchConfig): SearchConfigItem {
  const { modifier, value } = token;
  if (modifier) {
    return config[modifier];
  }
  if (value && (value !== ' ')) {
    return config['_default'];
  }
}
