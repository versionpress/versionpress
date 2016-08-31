import * as React from 'react';

import Buttons from './buttons/Buttons';
import Message from './Message';
import Spinner from './Spinner';
import * as portal from '../portal/portal';

import './ConfirmDialog.less';

interface ConfirmDialogProps {
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
    onOkButtonClick: function() {},
    onCancelButtonClick: function() {},
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

    return !isLoading
      ? <div className='ConfirmDialog'>
          <Message message={message} />
          <Buttons
            okButtonText={okButtonText}
            cancelButtonText={cancelButtonText}
            okButtonClassName={okButtonClasses}
            cancelButtonClassName={cancelButtonClasses}
            onOkClick={this.onOkClick}
            onCancelClick={this.onCancelClick}
          />
        </div>
      : <Spinner />;
  }

}
