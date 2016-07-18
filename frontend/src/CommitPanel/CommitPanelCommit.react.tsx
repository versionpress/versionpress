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

  constructor() {
    super();
    this.state = {
      isFormVisible: false
    };
    this.onSubmit = this.onSubmit.bind(this);
  }

  render() {
    return this.state.isFormVisible
      ? this.renderForm()
      : this.renderButtons();
  }

  onSubmit(e: React.SyntheticEvent) {
    e.preventDefault();

    const message = e.target['message'].value;

    if (this.props.onCommit(message)) {
      e.target['message'].value = '';
    }
  }

  onDiscard(e: React.MouseEvent) {
    e.preventDefault();
    const body = <div>This action cannot be undone, are you sure?</div>;
    const options = { okButtonText: 'Proceed' };

    portal.confirmDialog('Warning', body, this.props.onDiscard, () => {}, options);
  }

  private renderButtons() {
    return (
      <div className='CommitPanel-commit'>
        <a
          className='button button-primary CommitPanel-commit-button'
          onClick={() => this.displayForm()}
        >Commit changes</a>
        <a
          className='button CommitPanel-commit-button'
          onClick={this.onDiscard.bind(this)}
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
            onClick={() => this.hideForm()}
            type='button'
            value='Cancel'
          />
        </form>
      </div>
    );
  }

  private displayForm() {
    this.setState({
      isFormVisible: true
    });
  }

  private hideForm() {
    this.setState({
      isFormVisible: false
    });
  }

}
