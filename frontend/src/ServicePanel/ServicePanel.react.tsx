/// <reference path='../../typings/browser.d.ts' />

import * as React from 'react';
import config from '../config';

import './ServicePanel.less';

interface ServicePanelProps extends React.Props<JSX.Element> {
  display: boolean;
}

export default class ServicePanel extends React.Component<ServicePanelProps, {}> {

  render() {
    const className = 'ServicePanel-wrapper' + (this.props.display ? '' : ' ServicePanel-wrapper--hide');
    const systemInfoUrl = config.api.adminUrl + '/admin.php?page=versionpress/admin/system-info.php';

    return (
      <div className={className}>
        <div className='ServicePanel welcome-panel'>
          <div className='ServicePanel-inner'>
            <p className='ServicePanel-warning'>
              Currently, VersionPress is in an {' '}
              <a href='http://docs.versionpress.net/en/getting-started/about-eap'>
                <strong>Early Access phase</strong>
              </a>.<br />
              As such, it might not fully support certain workflows, 3rd party plugins, hosts etc.
            </p>

            <h3>Community and support</h3>
            <ul>
              <li>
                Having trouble using VersionPress?
                Our <a href='http://docs.versionpress.net'>documentation</a> has you covered.
              </li>
              <li>
                Can’t find what you need?
                Please visit our <a href='https://github.com/versionpress/support'>support&nbsp;repository</a>.
              </li>
              <li>
                <a href={systemInfoUrl}>System information</a> page.
              </li>
            </ul>
          </div>
        </div>
      </div>
    );
  }

}
