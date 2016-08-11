import * as React from 'react';

import OverviewLine from './OverviewLine';
import OverviewShowMore from './OverviewShowMore';

interface OverviewTabProps {
  gitStatus: VpApi.GetGitStatusResponse;
}

interface OverviewTabState {
  isExpanded: boolean;
}

export default class OverviewTab extends React.Component<OverviewTabProps, OverviewTabState> {

  state = {
    isExpanded: false,
  };

  private displayedListLength: number = 5;

  onShowMoreClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      isExpanded: true,
    });
  };

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
          {lines.map((line, i) => {
            return (
              <OverviewLine
                actionShortcut={line[0]}
                info={line[1]}
                key={i}
              />
            );
          })}
          {(gitStatus.length > displayedListLength && !isExpanded) &&
            <OverviewShowMore
              displayNumber={gitStatus.length - displayedListLength}
              onClick={this.onShowMoreClick}
            />
          }
        </ul>
      </div>
    );
  }

}
