/// <reference path='../Search.d.ts' />

import getAdapter from '../modifiers/getAdapter';
import { getModifier } from './tokenize';

export function updateToken(token: Token, model: SearchConfigItemContent, config: SearchConfig) {
  const adapter = getAdapter(config)(token);
  const val = adapter.serialize(model);

  if (model.modifier) {
    const modifier = getModifier(val, config);
    const value = modifier.length ? val.substr(modifier.length) : val;

    token.modifier = modifier;
    token.value = value;
    token.type = modifier ? config[modifier].type : config['_default'].type;
    token.length = modifier.length + value.length + (token.negative ? 1 : 0);
  } else {
    token.value = val;
    token.length = token.modifier.length + val.length + (token.negative ? 1 : 0);
  }

  return token;
}
