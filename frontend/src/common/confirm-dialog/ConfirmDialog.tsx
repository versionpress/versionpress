import * as React from 'react';

import * as portal from '../portal';
import Message from './Message';
import Buttons from './buttons/Buttons';
import Spinner from './Spinner';

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
