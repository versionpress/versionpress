import * as React from 'react';

import * as portal from '../common/portal';

interface CommitPanelCommitProps extends React.Props<JSX.Element> {
  onCommit: (message: string) => any;
  onDiscard: () => any;
}

interface CommitPanelCommitState {
  isFormVisible: boolean;
}

export default class CommitPanelCommit extends React.Component<CommitPanelCommitProps, CommitPanelCommitState> {

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

  onDiscard = (e: React.MouseEvent) => {
    e.preventDefault();
    const body = <div>This action cannot be undone, are you sure?</div>;
    const options = { okButtonText: 'Proceed' };

    portal.confirmDialog('Warning', body, this.props.onDiscard, () => {}, options);
  };

  onCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isFormVisible: true,
    });
  };

  onCancelCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isFormVisible: false,
    });
  };

  private renderButtons() {
    return (
      <div className='CommitPanel-commit'>
        <a
          className='button button-primary CommitPanel-commit-button'
          onClick={this.onCommitClick}
        >Commit changes</a>
        <a
          className='button CommitPanel-commit-button'
          onClick={this.onDiscard}
        >Discard changes</a>
      </div>
    );
  }

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
      : this.renderButtons();
  }

}
