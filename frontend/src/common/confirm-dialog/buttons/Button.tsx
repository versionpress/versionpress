import * as React from 'react';
import * as classNames from 'classnames';

interface ButtonProps {
  text: string;
  isPrimary: boolean;
  extraClassName: string;
  onClick(): void;
}

const Button: React.StatelessComponent<ButtonProps> = (props) => {
  const {
    text,
    isPrimary,
    extraClassName,
    onClick,
  } = props;
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
