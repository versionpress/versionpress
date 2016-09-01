/// <reference path='../Search.d.ts' />
/// <reference path='../modifiers/PopupComponent.d.ts' />

import * as React from 'react';

import getComponent from '../modifiers/getComponent';

interface PopupProps {
  activeToken: Token;
}

const Popup: React.StatelessComponent<PopupProps> = (props) => {
  const { activeToken } = props;

  const popupComponent = getComponent(activeToken);
  const popupComponentProps: PopupComponentProps = {

  };

  return (
    <div>
      {popupComponent &&
        React.createElement(popupComponent, popupComponentProps)
      }
    </div>
  );
};

export default Popup;
