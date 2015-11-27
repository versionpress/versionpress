/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';

import './ServicePanel.less';

interface ServicePanelProps extends React.Props<JSX.Element> {
  display: boolean;
  onSubmit: (values: Object) => boolean;
}

export default class ServicePanel extends React.Component<ServicePanelProps, {}> {

  onSubmit(e: React.SyntheticEvent) {
    e.preventDefault();

    const values = {
      email: e.target['email'].value,
      description: e.target['description'].value
    };

    if (this.props.onSubmit(values)) {
      e.target['email'].value = '';
      e.target['description'].value = '';
    }
  }

  render() {
    const className = 'ServicePanel-wrapper' + (this.props.display ? '' : ' ServicePanel-wrapper--hide');

    return (
      <div className={className}>
        <div className='ServicePanel welcome-panel'>
          <h3>VersionPress Service Panel</h3>
          <h4>Bug report</h4>
          <form action='' method='post' onSubmit={this.onSubmit.bind(this)}>
            <div className='ServicePanel-row'>
              <label className='ServicePanel-label' htmlFor='ServicePanel-email'>Email</label>
              <div className='ServicePanel-input' data-description='We will respond you to this email.'>
                <input
                  id='ServicePanel-email'
                  name='email'
                  type='email'
                />
              </div>
            </div>
            <div className='ServicePanel-row'>
              <label className='ServicePanel-label' htmlFor='ServicePanel-description'>Bug description</label>
              <div className='ServicePanel-input' data-description='Please tell us what you were doing when the bug occured.'>
                <textarea
                  className='ServicePanel-input'
                  name='description'
                  id='ServicePanel-description'
                />
              </div>
            </div>
            <input
              className='button submit'
              type='submit'
              value='Send bug report'
            />
          </form>
        </div>
      </div>
    );
  }

}
