/// <reference path='../Search.d.ts' />

import DefaultComponent from './default/Component';
import ListComponent from './list/Component';

export default function getComponent(activeToken: Token) {
  if (activeToken && activeToken.type && activeToken.type !== 'space') {
    const { type } = activeToken;

    if (type === 'list' || type === 'modifier-list') {
      return ListComponent;
    }
    /*
    if (type === 'date') {
      return DateComponent;
    }
    */

    return DefaultComponent;
  }
  return null;
}
