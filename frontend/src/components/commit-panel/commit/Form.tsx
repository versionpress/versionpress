import * as React from 'react';
import { observer } from 'mobx-react';

interface FormProps {
  commitMessage: string;
  onCommitMessageChange(value: string): void;
  onSubmit(): void;
  onCancelCommitClick(): void;
}

const Form: React.StatelessComponent<FormProps> = (props) => {
  const {
    commitMessage,
    onCommitMessageChange,
    onSubmit,
    onCancelCommitClick,
  } = props;

  return (
    <div className='CommitPanel-commit'>
      <form onSubmit={e => { e.preventDefault(); onSubmit(); }}>
        <textarea
          autoFocus={true}
          className='CommitPanel-commit-input'
          value={commitMessage}
          placeholder='Commit message...'
          onChange={e => onCommitMessageChange(e.currentTarget.value)}
        />
        <input
          className='button button-primary CommitPanel-commit-button'
          type='submit'
          value='Commit'
        />
        <input
          className='button CommitPanel-commit-button'
          onClick={e => { e.preventDefault(); onCancelCommitClick(); }}
          type='button'
          value='Cancel'
        />
      </form>
    </div>
  );
};

export default observer(Form);
