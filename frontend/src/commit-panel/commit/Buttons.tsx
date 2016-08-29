import * as React from 'react';

interface ButtonsProps {
  onCommitClick(e: React.MouseEvent): void;
  onDiscardClick(e: React.MouseEvent): void;
}

const Buttons: React.StatelessComponent<ButtonsProps> = ({ onCommitClick, onDiscardClick }) => (
  <div className='CommitPanel-commit'>
    <a
      className='button button-primary CommitPanel-commit-button'
      onClick={onCommitClick}
    >
      Commit changes
    </a>
    <a
      className='button CommitPanel-commit-button'
      onClick={onDiscardClick}
    >
      Discard changes
    </a>
  </div>
);

export default Buttons;
