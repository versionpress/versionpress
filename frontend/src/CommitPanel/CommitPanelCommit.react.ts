/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

const DOM = React.DOM;

interface CommitPanelCommitProps {
  onCommit: (message: string) => any;
  onDiscard: () => any;
}

interface CommitPanelCommitState {
  displayForm: boolean;
}

class CommitPanelCommit extends React.Component<CommitPanelCommitProps, CommitPanelCommitState> {

  constructor() {
    super();
    this.state = { displayForm: false };
    this.onSubmit = this.onSubmit.bind(this);
  }

  render() {
    return this.state.displayForm
      ? this.renderForm()
      : this.renderButtons();
  }

  private renderButtons() {
    return DOM.div({className: 'CommitPanel-commit'},
      DOM.a({
        className: 'button CommitPanel-commit-button',
        onClick: () => this.displayForm()
      }, 'Commit changes'),
      DOM.a({
        className: 'button CommitPanel-commit-button',
        onClick: () => this.props.onDiscard()
      }, 'Discard changes')
    );
  }

  private renderForm() {
    return DOM.div({className: 'CommitPanel-commit'},
      DOM.form({onSubmit: this.onSubmit},
        DOM.textarea({
          autoFocus: true,
          className: 'CommitPanel-commit-input',
          name: 'message',
          placeholder: 'Manual commit'
        }),
        DOM.input({
          className: 'button CommitPanel-commit-button',
          type: 'submit',
          value: 'Commit'
        }),
        DOM.input({
          className: 'button CommitPanel-commit-button',
          onClick: () => this.hideForm(),
          type: 'button',
          value: 'Close'
        })
      )
    );
  }

  onSubmit(e: React.SyntheticEvent) {
    e.preventDefault();

    const message = e.target['message'].value;

    if (this.props.onCommit(message)) {
      e.target['message'].value = '';
    }
  }

  private displayForm() {
    this.setState({ displayForm: true });
  }

  private hideForm() {
    this.setState({ displayForm: false });
  }

}

module CommitPanelCommit {
  export interface Props extends CommitPanelCommitProps {}
}

export = CommitPanelCommit;
