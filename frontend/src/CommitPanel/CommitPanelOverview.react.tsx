import * as React from 'react';

interface CommitPanelOverviewProps extends React.Props<JSX.Element> {
  gitStatus: VpApi.GetGitStatusResponse;
}

interface CommitPanelOverviewState {
  isExpanded: boolean;
}

export default class CommitPanelOverview extends React.Component<CommitPanelOverviewProps, CommitPanelOverviewState> {

  state = {
    isExpanded: false
  }

  private displayedListLength: number = 5;

  onShowMoreClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isExpanded: true,
    });
  };

  private getActionVerb(action: string) {
    if (action === 'M') {
      return 'Modified';
    } else if (action === '??' || action === 'A' || action === 'AM') {
      return 'Added';
    } else if (action === 'D') {
      return 'Deleted';
    }
  }

  render() {
    const { gitStatus } = this.props;
    const { isExpanded } = this.state;
    const displayedListLength = this.displayedListLength;

    let lines = [];
    if (isExpanded) {
      lines = gitStatus;
    } else {
      lines = gitStatus.slice(0, displayedListLength);
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
          {gitStatus.length > displayedListLength && !isExpanded
            ? (
              <li>
                <a onClick={this.onShowMoreClick}>
                  show {gitStatus.length - displayedListLength} more...
                </a>
              </li>
            ) : null
          }
        </ul>
      </div>
    );
  }

}
