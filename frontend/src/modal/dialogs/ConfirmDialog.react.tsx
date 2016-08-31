import * as React from 'react';

import * as portal from '../portal';

import './ConfirmDialog.less';

interface ConfirmDialogProps extends React.Props<JSX.Element> {
  message?: React.ReactNode;
  okButtonText?: string;
  cancelButtonText?: string;
  okButtonClickHandler?: Function;
  cancelButtonClickHandler?: Function;
  okButtonClasses?: string;
  cancelButtonClasses?: string;
  isLoading?: boolean;
}

export default class ConfirmDialog extends React.Component<ConfirmDialogProps, {}> {

  static defaultProps = {
    okButtonText: 'OK',
    cancelButtonText: 'Cancel',
    okButtonClickHandler: function() {},
    cancelButtonClickHandler: function() {},
    isLoading: false,
  };

  onOkClick = () => {
    if (this.props.okButtonClickHandler() !== false) {
      portal.closePortal();
    }
  };

  onCancelClick = () => {
    if (this.props.cancelButtonClickHandler() !== false) {
      portal.closePortal();
    }
  };

  render() {
    const okButtonClasses = 'ConfirmDialog-button button button-primary ' + this.props.okButtonClasses;
    const cancelButtonClasses = 'ConfirmDialog-button button ' + this.props.cancelButtonClasses;

    return !this.props.isLoading
      ? <div className='ConfirmDialog'>
          <div className='ConfirmDialog-message'>{this.props.message}</div>
          <div className='ConfirmDialog-buttons'>
            <button className={okButtonClasses} onClick={this.onOkClick}>
              {this.props.okButtonText}
            </button>
            <button className={cancelButtonClasses} onClick={this.onCancelClick}>
              {this.props.cancelButtonText}
            </button>
          </div>
        </div>
      : <div className='ConfirmDialog-spinner' />;
  }

}
