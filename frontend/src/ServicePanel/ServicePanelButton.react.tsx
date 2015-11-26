/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';

import './ServicePanelButton.less';

interface ServicePanelButtonProps extends React.Props<JSX.Element> {
  onClick: React.MouseEventHandler;
}

export default class ServicePanelButton extends React.Component<ServicePanelButtonProps, {}> {

  render() {
    return (
      <button className='ServicePanelButton' onClick={this.props.onClick}>
        <span className='icon icon-cog' />
      </button>
    );
  }

}
