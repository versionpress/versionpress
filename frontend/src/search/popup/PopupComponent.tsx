/// <reference path='../../../typings/browser.d.ts' />
/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

import getComponent from '../modifiers/getComponent';
import ModifierComponent from '../modifiers/ModifierComponent';

import './PopupComponent.less';

export interface PopupComponentProps {
  nodeRef: __React.Ref<ModifierComponent>;
  activeTokenIndex: number;
  token: Token;
  adapter: Adapter;
  onChangeTokenModel(tokenIndex: number, model: SearchConfigItemContent, shouldMoveCursor: boolean): void;
}

const PopupComponent: React.StatelessComponent<PopupComponentProps> = (props) => {
  const { nodeRef, token } = props;
  const childProps: PopupComponentProps = Object.assign({}, props, { ref: nodeRef });

  const popupComponent = getComponent(token);

  if (!popupComponent) {
    return <div />;
  }

  return (
    <div className='Search-hintMenu-container'>
      <span className='Search-hintMenu-arrow' />
      <span className='Search-hintMenu-arrowBorder' />
      {popupComponent &&
        React.createElement(popupComponent, childProps)
      }
    </div>
  );
};

export default PopupComponent;
