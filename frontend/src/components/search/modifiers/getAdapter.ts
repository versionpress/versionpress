/// <reference path='../Search.d.ts' />
/// <reference path='./Adapter.d.ts' />

import ListAdapter from './list/adapter';
import DateAdapter from './date/adapter';

export default function getAdapter(config: SearchConfig) {
  return (token: Token): Adapter => {
    const configItem = getConfigItem(token, config);
    const type = token && token.type;

    if (type === 'date') {
      return new DateAdapter(configItem);
    }

    return new ListAdapter(configItem);
  };
}

function getConfigItem(token: Token, config: SearchConfig): SearchConfigItem {
  if (!token || !token.modifier) {
    return config['_default'];
  }

  return config[token.modifier];
}
