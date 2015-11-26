/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';
import * as portal from './portal';

import './ConfirmDialog.less';

interface ConfirmDialogProps extends React.Props<JSX.Element> {
  message?: React.ReactNode;
  okButtonText?: string;
  cancelButtonText?: string;
  okButtonClickHandler?: Function;
  cancelButtonClickHandler?: Function;
  okButtonClasses?: string;
  cancelButtonClasses?: string;
  loading?: boolean;
}

export default class ConfirmDialog extends React.Component<ConfirmDialogProps, {}> {

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
      ? <div className='ConfirmDialog'>
          <div className='ConfirmDialog-message'>{this.props.message}</div>
          <div className='ConfirmDialog-buttons'>
            <button className={okButtonClasses} onClick={this.handleOkClick}>
              {this.props.okButtonText}
            </button>
            <button className={cancelButtonClasses} onClick={this.handleCancelClick}>
              {this.props.cancelButtonText}
            </button>
          </div>
        </div>
      : <div className='ConfirmDialog-spinner' />;
  }
}
