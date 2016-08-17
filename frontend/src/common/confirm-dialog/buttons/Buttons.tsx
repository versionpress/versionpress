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
        extraClassName={okButtonClassName}
        onClick={onOkClick}
      />
      <Button
        text={cancelButtonText}
        isPrimary={false}
        extraClassName={cancelButtonClassName}
        onClick={onCancelClick}
      />
    </div>
  );
};

export default Buttons;
