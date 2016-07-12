import * as React from 'react';

interface CommitPanelOverviewProps extends React.Props<JSX.Element> {
  gitStatus: VpApi.GetGitStatusResponse;
}

interface CommitPanelOverviewState {
  isExpanded: boolean;
}

export default class CommitPanelOverview extends React.Component<CommitPanelOverviewProps, CommitPanelOverviewState> {

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

    return (
      <div className='CommitPanel-overview'>
        <ul>
          {lines.map(line => {
            return (
              <li>
                <strong>{this.getActionVerb(line[0])}</strong>
                <span>{line[1]}</span>
              </li>
            );
          })}
          {this.props.gitStatus.length > this.displayedListLength && !this.state.isExpanded
            ? (
              <li>
                <a onClick={() => this.expand()}>
                  show {this.props.gitStatus.length - this.displayedListLength} more...
                </a>
              </li>
            ) : null
          }
        </ul>
      </div>
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
