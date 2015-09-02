/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import portal = require('./portal');

require('./ConfirmDialog.less');

const DOM = React.DOM;

interface ConfirmDialogProps {
  message?: React.ReactNode;
  okButtonText?: string;
  cancelButtonText?: string;
  okButtonClickHandler?: Function;
  cancelButtonClickHandler?: Function;
  okButtonClasses?: string;
  cancelButtonClasses?: string;
  loading?: boolean;
}

class ConfirmDialog extends React.Component<ConfirmDialogProps, {}> {

  constructor(props) {
    super(props);

    this.handleOkClick = this.handleOkClick.bind(this);
    this.handleCancelClick = this.handleCancelClick.bind(this);
  }

  static defaultProps = {
    okButtonText: 'OK',
    cancelButtonText: 'Cancel',
    okButtonClickHandler: function() {},
    cancelButtonClickHandler: function() {},
    loading: false
  };

  handleOkClick() {
    if (this.props.okButtonClickHandler() !== false) {
      portal.closePortal();
    }
  }

  handleCancelClick() {
    if (this.props.cancelButtonClickHandler() !== false) {
      portal.closePortal();
    }
  }

  render() {
    const okButtonClasses = 'ConfirmDialog-button button button-primary ' + this.props.okButtonClasses;
    const cancelButtonClasses = 'ConfirmDialog-button button ' + this.props.cancelButtonClasses;

    return !this.props.loading
      ? DOM.div({className: 'ConfirmDialog'},
        DOM.div({className: 'ConfirmDialog-message'}, this.props.message),
          DOM.div({className: 'ConfirmDialog-buttons'},
            DOM.button({className: okButtonClasses, onClick: this.handleOkClick},
              this.props.okButtonText
            ),
            DOM.button({className: cancelButtonClasses, onClick: this.handleCancelClick},
              this.props.cancelButtonText
            )
          )
        )
      : DOM.div({className: 'ConfirmDialog-spinner'});
  }
}

module ConfirmDialog {
  export interface Props extends ConfirmDialogProps {}
}

export = ConfirmDialog;
