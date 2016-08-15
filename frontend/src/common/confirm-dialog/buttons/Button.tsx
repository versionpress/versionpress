import * as React from 'react';
import * as classNames from 'classnames';

interface OkButtonProps {
  text: string;
  extraClassName: string;
  isPrimary: boolean;
  onClick(): void;
}

const OkButton: React.StatelessComponent<OkButtonProps> = ({
  text, extraClassName, isPrimary, onClick
}) => {
  const buttonClassName = classNames({
    'ConfirmDialog-button': true,
    'button': true,
    'button-primary': isPrimary,
    [extraClassName]: !!extraClassName,
  });

  return (
    <button
      className={buttonClassName}
      onClick={onClick}
    >
      {text}
    </button>
  );
};

export default OkButton;
