/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/Adapter.d.ts' />

import * as React from 'react';

import PopupComponent from './PopupComponent';
import ModifierComponent from '../modifiers/ModifierComponent';

interface PopupProps {
  nodeRef?: React.Ref<ModifierComponent>;
  activeTokenIndex: number;
  activeToken: Token;
  getAdapter(token: Token): Adapter;
  onChangeTokenModel(tokenIndex: number, model: SearchConfigItemContent, shouldMoveCursor: boolean): void;
}

const Popup: React.StatelessComponent<PopupProps> = (props) => {
  const {
    nodeRef,
    activeTokenIndex,
    activeToken,
    getAdapter,
    onChangeTokenModel,
  } = props;

  const displayPopupComponent = activeToken && activeToken.type && activeToken.type !== 'space';

  return (
    <div>
      {displayPopupComponent &&
        <PopupComponent
          nodeRef={nodeRef}
          activeTokenIndex={activeTokenIndex}
          token={activeToken}
          adapter={getAdapter(activeToken)}
          onChangeTokenModel={onChangeTokenModel}
        />
      }
    </div>
  );
};

export default Popup;
