import * as React from 'react';

import * as portal from '../../common/portal';
import Buttons from './Buttons';

interface CommitProps {
  onCommit(message: string): void;
  onDiscard(): void;
}

interface CommitState {
  isFormVisible: boolean;
}

export default class Commit extends React.Component<CommitProps, CommitState> {

  state = {
    isFormVisible: false,
  };

  onSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    const message = e.target['message'].value;

    if (this.props.onCommit(message)) {
      e.target['message'].value = '';
    }
  };

  onCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isFormVisible: true,
    });
  };

  onDiscardClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const body = <div>This action cannot be undone, are you sure?</div>;
    const options = { okButtonText: 'Proceed' };

    portal.confirmDialog('Warning', body, this.props.onDiscard, () => {}, options);
  };

  onCancelCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isFormVisible: false,
    });
  };

  private renderForm() {
    return (
      <div className='CommitPanel-commit'>
        <form onSubmit={this.onSubmit}>
          <textarea
            autoFocus={true}
            className='CommitPanel-commit-input'
            name='message'
            placeholder='Commit message...'
          />
          <input
            className='button button-primary CommitPanel-commit-button'
            type='submit'
            value='Commit'
          />
          <input
            className='button CommitPanel-commit-button'
            onClick={this.onCancelCommitClick}
            type='button'
            value='Cancel'
          />
        </form>
      </div>
    );
  }

  render() {
    return this.state.isFormVisible
      ? this.renderForm()
      : <Buttons
          onCommitClick={this.onCommitClick}
          onDiscardClick={this.onDiscardClick}
        />;
  }

}
