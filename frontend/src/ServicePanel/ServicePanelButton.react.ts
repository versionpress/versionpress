/// <reference path='../../typings/typings.d.ts' />

import React = require('react');

require('./ServicePanelButton.less');

const DOM = React.DOM;

interface ServicePanelButtonProps {
  onClick: React.MouseEventHandler;
}

class ServicePanelButton extends React.Component<ServicePanelButtonProps, {}> {

  render() {
    return DOM.button({
        className: 'ServicePanelButton',
        onClick: this.props.onClick
      },
      DOM.span({className: 'icon icon-cog'})
    );
  }

}

module ServicePanelButton {
  export interface Props extends ServicePanelButtonProps {}
}

export = ServicePanelButton;
