import * as React from 'react';

interface ButtonsProps {
  onCommitClick(): void;
  onDiscardClick(): void;
}

const Buttons: React.StatelessComponent<ButtonsProps> = ({ onCommitClick, onDiscardClick }) => (
  <div className='CommitPanel-commit'>
    <a
      className='button button-primary CommitPanel-commit-button'
      onClick={e => { e.preventDefault(); onCommitClick(); }}
    >
      Commit changes
    </a>
    <a
      className='button CommitPanel-commit-button'
      onClick={e => { e.preventDefault(); onDiscardClick(); }}
    >
      Discard changes
    </a>
  </div>
);

export default Buttons;
