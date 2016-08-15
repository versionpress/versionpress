import * as React from 'react';
import * as classNames from 'classnames';

interface ButtonProps {
  text: string;
  extraClassName: string;
  isPrimary: boolean;
  onClick(): void;
}

const Button: React.StatelessComponent<ButtonProps> = ({
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

export default Button;
