/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';

import './ServicePanel.less';

interface ServicePanelProps extends React.Props<JSX.Element> {
  display: boolean;
}

export default class ServicePanel extends React.Component<ServicePanelProps, {}> {

  render() {
    const className = 'ServicePanel-wrapper' + (this.props.display ? '' : ' ServicePanel-wrapper--hide');

    return (
      <div className={className}>
        <div className='ServicePanel welcome-panel'>
          <h3>VersionPress Service Panel</h3>
        </div>
      </div>
    );
  }

}
