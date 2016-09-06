/// <reference path='../Search.d.ts' />

import getAdapter from '../modifiers/getAdapter';
import { getModifier } from './tokenize';

export function updateToken(token: Token, model: SearchConfigItemContent, config: SearchConfig) {
  const adapter = getAdapter(config)(token);
  const val = adapter.serialize(model);

  if (model.modifier) {
    const modifier = getModifier(val, config);
    const value = modifier ? val.substr(modifier.length) : val;

    token.modifier = modifier;
    token.value = value;
    token.type = modifier ? config[modifier].type : config['_default'].type;
    token.length = modifier ? modifier.length + value.length : value.length;
  } else {
    token.value = val;
    token.length = token.modifier ? token.modifier.length + val.length : val.length;
  }

  return token;
}
