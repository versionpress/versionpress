/// <reference path='../Search.d.ts' />

import ListComponent from './list/Component';
import DateComponent from './date/Component';

export default function getComponent(activeToken: Token) {
  const type = activeToken && activeToken.type;

  if (type === 'date') {
    return DateComponent;
  }

  return ListComponent;
}
