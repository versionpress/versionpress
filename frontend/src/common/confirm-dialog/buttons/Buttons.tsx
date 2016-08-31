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

const Buttons: React.StatelessComponent<ButtonsProps> = (props) => {
  const {
    okButtonText,
    cancelButtonText,
    okButtonClassName,
    cancelButtonClassName,
    onOkClick,
    onCancelClick,
  } = props;

  return (
    <div className='ConfirmDialog-buttons'>
      <Button
        text={okButtonText}
        isPrimary={true}
        className={okButtonClassName}
        onClick={onOkClick}
      />
      <Button
        text={cancelButtonText}
        isPrimary={false}
        className={cancelButtonClassName}
        onClick={onCancelClick}
      />
    </div>
  );
};

export default Buttons;
