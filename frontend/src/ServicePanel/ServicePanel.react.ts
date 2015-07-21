/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

require('./ServicePanel.less');

const DOM = React.DOM;

interface ServicePanelProps {
  display: boolean;
  onSubmit: (values: Object) => boolean;
}

class ServicePanel extends React.Component<ServicePanelProps, any> {

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
    const className = 'ServicePanel welcome-panel' + (this.props.display ? '' : ' hide');

    return DOM.div({className: className},
      DOM.h3(null, 'VersionPress Service Panel'),
      DOM.h4(null, 'Bug report'),
      DOM.form({
        action: '',
        method: 'post',
        onSubmit: this.onSubmit.bind(this)
      },
        DOM.div({className: 'ServicePanel-row'},
          DOM.label({
            className: 'ServicePanel-label',
            htmlFor: 'ServicePanel-email'
          }, 'Email'),
          DOM.input({
            className: 'ServicePanel-input',
            id: 'ServicePanel-email',
            name: 'email',
            type: 'email',
            dataDescription: 'We will respond you to this email.'
          })
        ),
        DOM.div({className: 'ServicePanel-row'},
          DOM.label({
            className: 'ServicePanel-label',
            htmlFor: 'ServicePanel-description'
          }, 'Bug description'),
          DOM.textarea({
            className: 'ServicePanel-input',
            name: 'description',
            id: 'ServicePanel-description',
            dataDescription: 'Please tell us what you were doing when the bug occured.'
          })
        ),
        DOM.input({
          className: 'button submit',
          type: 'submit',
          value: 'Send bug report'
        })
      )
    );
  }

}

module ServicePanel {
  export interface Props extends ServicePanelProps {}
}

export = ServicePanel;
