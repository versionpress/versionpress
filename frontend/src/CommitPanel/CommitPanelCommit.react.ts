/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

import portal = require('../common/portal');

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

  onSubmit(e: React.SyntheticEvent) {
    e.preventDefault();

    const message = e.target['message'].value;

    if (this.props.onCommit(message)) {
      e.target['message'].value = '';
    }
  }

  onDiscard(e: React.MouseEvent) {
    e.preventDefault();
    const body = DOM.div(null, 'This action cannot be undone, are you sure?');
    const options = { okButtonText: 'Proceed' };

    portal.confirmDialog('Warning', body, this.props.onDiscard, () => {}, options);
  }

  private renderButtons() {
    return DOM.div({className: 'CommitPanel-commit'},
      DOM.a({
        className: 'button button-primary CommitPanel-commit-button',
        onClick: () => this.displayForm()
      }, 'Commit changes'),
      DOM.a({
        className: 'button CommitPanel-commit-button',
        onClick: this.onDiscard.bind(this)
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
          placeholder: 'Commit message...'
        }),
        DOM.input({
          className: 'button button-primary CommitPanel-commit-button',
          type: 'submit',
          value: 'Commit'
        }),
        DOM.input({
          className: 'button CommitPanel-commit-button',
          onClick: () => this.hideForm(),
          type: 'button',
          value: 'Cancel'
        })
      )
    );
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
