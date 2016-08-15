import * as React from 'react';

import Button from './Button';

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
    <Button
      text={okButtonText}
      extraClassName={okButtonClassName}
      isPrimary={true}
      onClick={onOkClick}
    />
    <Button
      text={cancelButtonText}
      extraClassName={cancelButtonClassName}
      isPrimary={false}
      onClick={onCancelClick}
    />
  </div>
);

export default Buttons;
