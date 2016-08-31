import * as React from 'react';
import * as classNames from 'classnames';

interface ButtonProps {
  text: string;
  isPrimary: boolean;
  className: string;
  onClick(): void;
}

const Button: React.StatelessComponent<ButtonProps> = (props) => {
  const { text, isPrimary, className, onClick } = props;

  const buttonClassName = classNames({
    'ConfirmDialog-button': true,
    'button': true,
    'button-primary': isPrimary,
    [className]: !!className,
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
