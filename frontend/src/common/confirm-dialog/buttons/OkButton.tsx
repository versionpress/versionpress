import * as React from 'react';

interface OkButtonProps {
  text: string;
  extraClassName: string;
  onClick(): void;
}

const OkButton: React.StatelessComponent<OkButtonProps> = ({ text, extraClassName, onClick }) => (
  <button
    className={`ConfirmDialog-button button button-primary ${extraClassName}`}
    onClick={onClick}
  >
    {text}
  </button>
);

export default OkButton;
