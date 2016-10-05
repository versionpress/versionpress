import * as React from 'react';
import { observer } from 'mobx-react';
import { observable } from 'mobx';

import Buttons from './Buttons';
import Form from './Form';
import * as portal from '../../portal/portal';

interface CommitProps {
  onCommit(message: string): void;
  onDiscard(): void;
}

@observer
export default class Commit extends React.Component<CommitProps, {}> {

  @observable isFormVisible: boolean = false;
  @observable commitMessage: string = '';

  onSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    this.props.onCommit(this.commitMessage);

    this.commitMessage = '';
  };

  onCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.isFormVisible = true;
  };

  onCommitMessageChange = (e: React.FormEvent) => {
    this.commitMessage = (e.target as HTMLTextAreaElement).value;
  };

  onCancelCommitClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.isFormVisible = false;
  };

  onDiscardClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const body = <div>This action cannot be undone, are you sure?</div>;
    const options = {
      okButtonText: 'Proceed',
      onOkButtonClick: this.props.onDiscard,
    };

    portal.confirmDialog('Warning', body, options);
  };

  render() {
    return this.isFormVisible
      ? <Form
          commitMessage={this.commitMessage}
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
