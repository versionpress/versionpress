import * as React from 'react';

interface CancelButtonProps {
  text: string;
  extraClassName: string;
  onClick(): void;
}

const CancelButton: React.StatelessComponent<CancelButtonProps> = ({ text, extraClassName, onClick }) => (
  <button
    className={`ConfirmDialog-button button ${extraClassName}`}
    onClick={onClick}
  >
    {text}
  </button>
);

export default CancelButton;
