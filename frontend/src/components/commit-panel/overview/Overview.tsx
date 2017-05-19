import * as React from 'react';
import { observer } from 'mobx-react';

import Item from './Item';
import ShowMore from './ShowMore';

interface OverviewProps {
  gitStatus: VpApi.GetGitStatusResponse;
}

interface OverviewState {
  isExpanded: boolean;
}

@observer
export default class Overview extends React.Component<OverviewProps, OverviewState> {

  private static displayedListLength: number = 5;

  state = {
    isExpanded: false,
  };

  onShowMoreClick = () => {
    this.setState({
      isExpanded: true,
    });
  }

  render() {
    const { gitStatus } = this.props;
    const { isExpanded } = this.state;

    const displayedListLength = Overview.displayedListLength;
    const lines = isExpanded
      ? gitStatus
      : gitStatus.slice(0, displayedListLength);

    return (
      <div className='CommitPanel-overview'>
        <ul>
          {lines.map((line, i) => (
            <Item
              actionShortcut={line[0]}
              info={line[1]}
              key={i}
            />
          ))}
          {(gitStatus.length > displayedListLength && !isExpanded) &&
            <ShowMore
              displayNumber={gitStatus.length - displayedListLength}
              onClick={this.onShowMoreClick}
            />
          }
        </ul>
      </div>
    );
  }

}
