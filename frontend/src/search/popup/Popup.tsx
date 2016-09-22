/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

import getComponent from '../modifiers/getComponent';
import ModifierComponent from '../modifiers/ModifierComponent';

import './Popup.less';

export interface PopupProps {
  nodeRef: __React.Ref<ModifierComponent<any>>;
  activeTokenIndex: number;
  token: Token;
  adapter: Adapter;
  onChangeTokenModel(tokenIndex: number, model: SearchConfigItemContent, shouldMoveCursor: boolean): void;
}

const Popup: React.StatelessComponent<PopupProps> = (props) => {
  const { nodeRef, token } = props;
  const childProps: PopupProps = Object.assign({}, props, { ref: nodeRef });

  const popupComponent = getComponent(token);

  return React.createElement(popupComponent, childProps);
};

export default Popup;
