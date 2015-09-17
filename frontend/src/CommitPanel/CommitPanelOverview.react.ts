/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

const DOM = React.DOM;

interface CommitPanelOverviewProps {
  gitStatus: string[][];
}

interface CommitPanelOverviewState {
  isExpanded: boolean;
}

class CommitPanelOverview extends React.Component<CommitPanelOverviewProps, CommitPanelOverviewState> {

  displayedListLength = 5;

  constructor() {
    super();
    this.state = { isExpanded: false };
  }

  render() {
    let lines = [];
    if (this.state.isExpanded) {
      lines = this.props.gitStatus;
    } else {
      lines = this.props.gitStatus.slice(0, this.displayedListLength);
    }

    return DOM.div({className: 'CommitPanel-overview'},
      DOM.ul(null,
        lines.map(line => {
          return DOM.li(null,
            DOM.strong(null, this.getActionVerb(line[0])),
            DOM.span(null, line[1])
          );
        }),
        this.props.gitStatus.length > this.displayedListLength && !this.state.isExpanded
          ? DOM.li(null, DOM.a({onClick: () => this.expand()}, 'show ', this.props.gitStatus.length - this.displayedListLength, ' more...'))
          : null
      )
    );
  }

  private expand() {
    this.setState({ isExpanded: true });
  }

  private getActionVerb(action: string) {
    if (action === 'M') {
      return 'Modified';
    } else if (action === '??' || action === 'A' || action === 'AM') {
      return 'Added';
    } else if (action === 'D') {
      return 'Deleted';
    }
  }

}

module CommitPanelOverview {
  export interface Props extends CommitPanelOverviewProps {}
}

export = CommitPanelOverview;
