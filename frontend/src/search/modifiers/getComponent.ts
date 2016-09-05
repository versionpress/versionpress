/// <reference path='../Search.d.ts' />

import ListComponent from './list/Component';

export default function getComponent(activeToken: Token) {
  if (activeToken && activeToken.type && activeToken.type !== 'space') {

    /*
    const { type } = activeToken;
    if (type === 'date') {
      return DateComponent;
    }
    */

    return ListComponent;
  }
  return null;
}
