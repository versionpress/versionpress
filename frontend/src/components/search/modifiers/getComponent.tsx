/// <reference path='../Search.d.ts' />

import ModifierComponent from './ModifierComponent';

import ListComponent from './list/Component';
import DateComponent from './date/Component';

export default function getComponent(activeToken: Token): typeof ModifierComponent {
  const type = activeToken && activeToken.type;

  if (type === 'date') {
    return DateComponent;
  }

  return ListComponent;
}
