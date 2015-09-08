/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

const DOM = React.DOM;

interface CommitPanelNoticeProps {
  onDetailsLevelChanged: (detailsLevel) => any;
  detailsLevel: string;
}

class CommitPanelNotice extends React.Component<CommitPanelNoticeProps, {}> {

  render() {
    return DOM.p(null,
      'You have ',
      DOM.a({
        href: 'http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files',
        target: '_blank'
      }, 'uncommitted changes'),
      ' in your WordPress directory. ',
      DOM.a({className: 'CommitPanel-notice-toggle', onClick: () => this.toggleDetails()},
        'Click here to ' +
        (this.props.detailsLevel === 'none' ? 'show' : 'hide')
        + ' changes.'
      ),
      this.renderButtons()
    );
  }

  private renderButtons() {
    return this.props.detailsLevel !== 'none' ? DOM.div({className: 'CommitPanel-notice-buttons'},
      DOM.button({
        className: 'button',
        disabled: this.props.detailsLevel === 'overview',
        onClick: () => this.changeDetailsLevel('overview')
      }, 'Overview'),
      DOM.button({
        className: 'button',
        disabled: this.props.detailsLevel === 'full-diff',
        onClick: () => this.changeDetailsLevel('full-diff')
      }, 'Full diff')
    ) : null;
  }

  private toggleDetails() {
    this.changeDetailsLevel(this.props.detailsLevel === 'none' ? 'overview' : 'none');
  }

  private changeDetailsLevel(detailsLevel) {
    this.props.onDetailsLevelChanged(detailsLevel);
  }

}

module CommitPanelNotice {
  export interface Props extends CommitPanelNoticeProps {}
}

export = CommitPanelNotice;
