import * as React from 'react';
import { observer } from 'mobx-react';

import Buttons from './Buttons';
import Form from './Form';
import * as portal from '../../portal/portal';

interface CommitProps {
  onCommit(message: string): void;
  onDiscard(): void;
}

interface CommitState {
  isFormVisible?: boolean;
  commitMessage?: string;
}

@observer
export default class Commit extends React.Component<CommitProps, CommitState> {

  state = {
    isFormVisible: false,
    commitMessage: '',
  };

  onSubmit = () => {
    this.props.onCommit(this.state.commitMessage);

    this.setState({
      commitMessage: '',
    });
  }

  onCommitClick = () => {
    this.setState({
      isFormVisible: true,
    });
  }

  onCommitMessageChange = (value: string) => {
    this.setState({
      commitMessage: value,
    });
  }

  onCancelCommitClick = () => {
    this.setState({
      isFormVisible: false,
    });
  }

  onDiscardClick = () => {
    const body = <div>This action cannot be undone, are you sure?</div>;
    const options = {
      okButtonText: 'Proceed',
      onOkButtonClick: this.props.onDiscard,
    };

    portal.confirmDialog('Warning', body, options);
  }

  render() {
    const { isFormVisible, commitMessage } = this.state;

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
