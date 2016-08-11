import * as React from 'react';

import * as portal from '../../common/portal';
import Buttons from './Buttons';
import Form from './Form';

interface CommitProps {
  onCommit(message: string): void;
  onDiscard(): void;
}

interface CommitState {
  isFormVisible?: boolean;
  commitMessage?: string;
}

export default class Commit extends React.Component<CommitProps, CommitState> {

  state = {
    isFormVisible: false,
    commitMessage: '',
  };

  onSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    this.props.onCommit(this.state.commitMessage);

    this.setState({
      commitMessage: '',
    });
  };

  onCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isFormVisible: true,
    });
  };

  onCommitMessageChange = (e: React.FormEvent) => {
    this.setState({
      commitMessage: (e.target as any).value,
    });
  };

  onCancelCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isFormVisible: false,
    });
  };

  onDiscardClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const body = <div>This action cannot be undone, are you sure?</div>;
    const options = { okButtonText: 'Proceed' };

    portal.confirmDialog('Warning', body, this.props.onDiscard, () => {}, options);
  };

  render() {
    const { isFormVisible, commitMessage} = this.state;

    return isFormVisible
      ? <Form
          commitMessage={commitMessage}
          onCommitMessageChange={this.onCommitMessageChange}
          onSubmit={this.onSubmit}
          onCancelCommitClick={this.onCancelCommitClick}
        />
      : <Buttons
          onCommitClick={this.onCommitClick}
          onDiscardClick={this.onDiscardClick}
        />;
  }

}
