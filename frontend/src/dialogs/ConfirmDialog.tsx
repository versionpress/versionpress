import * as React from 'react';
import * as classNames from 'classnames';

import * as portal from '../portal/portal';

import './ConfirmDialog.less';

export interface ConfirmDialogProps {
  message?: React.ReactNode;
  okButtonText?: string;
  cancelButtonText?: string;
  okButtonClasses?: string;
  cancelButtonClasses?: string;
  isLoading?: boolean;
  onOkButtonClick?(): void | boolean;
  onCancelButtonClick?(): void | boolean;
}

export default class ConfirmDialog extends React.Component<ConfirmDialogProps, {}> {

  static defaultProps = {
    okButtonText: 'OK',
    cancelButtonText: 'Cancel',
    isLoading: false,
    onOkButtonClick: () => {},
    onCancelButtonClick: () => {},
  };

  onOkClick = () => {
    if (this.props.onOkButtonClick() !== false) {
      portal.closePortal();
    }
  };

  onCancelClick = () => {
    if (this.props.onCancelButtonClick() !== false) {
      portal.closePortal();
    }
  };

  render() {
    const {
      message,
      okButtonText,
      cancelButtonText,
      okButtonClasses,
      cancelButtonClasses,
      isLoading,
    } = this.props;

    const okButtonClassName = classNames({
      'ConfirmDialog-button': true,
      'button': true,
      'button-primary': true,
      [okButtonClasses]: !!okButtonClasses,
    });

    const cancelButtonClassName = classNames({
      'ConfirmDialog-button': true,
      'button': true,
      [cancelButtonClasses]: !!cancelButtonClasses,
    });

    return !isLoading
      ? <div className='ConfirmDialog'>
          <div className='ConfirmDialog-message'>{message}</div>
          <div className='ConfirmDialog-buttons'>
            <button className={okButtonClassName} onClick={this.onOkClick}>
              {okButtonText}
            </button>
            <button className={cancelButtonClassName} onClick={this.onCancelClick}>
              {cancelButtonText}
            </button>
          </div>
        </div>
      : <div className='ConfirmDialog-spinner' />;
  }

}
