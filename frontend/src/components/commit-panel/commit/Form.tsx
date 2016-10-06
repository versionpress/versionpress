import * as React from 'react';
import { observer } from 'mobx-react';

interface FormProps {
  commitMessage: string;
  onCommitMessageChange(e: React.FormEvent): void;
  onSubmit(e: React.FormEvent): void;
  onCancelCommitClick(e: React.MouseEvent): void;
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
      <form onSubmit={onSubmit}>
        <textarea
          autoFocus={true}
          className='CommitPanel-commit-input'
          value={commitMessage}
          placeholder='Commit message...'
          onChange={onCommitMessageChange}
        />
        <input
          className='button button-primary CommitPanel-commit-button'
          type='submit'
          value='Commit'
        />
        <input
          className='button CommitPanel-commit-button'
          onClick={onCancelCommitClick}
          type='button'
          value='Cancel'
        />
      </form>
    </div>
  );
};

export default observer(Form);
