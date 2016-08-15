import * as React from 'react';

import OkButton from './OkButton';
import CancelButton from './CancelButton';

interface ButtonsProps {
  okButtonText: string;
  cancelButtonText: string;
  okButtonClassName: string;
  cancelButtonClassName: string;
  onOkClick(): void;
  onCancelClick(): void;
}

const Buttons: React.StatelessComponent<ButtonsProps> = ({
  okButtonText, cancelButtonText, okButtonClassName, cancelButtonClassName, onOkClick, onCancelClick,
}) => (
  <div className='ConfirmDialog-buttons'>
    <OkButton
      text={okButtonText}
      extraClassName={okButtonClassName}
      onClick={onOkClick}
    />
    <CancelButton
      text={cancelButtonText}
      extraClassName={cancelButtonClassName}
      onClick={onCancelClick}
    />
  </div>
);

export default Buttons;
