import * as React from 'react';
import { observer } from 'mobx-react';
import { observable } from 'mobx';

import Item from './Item';
import ShowMore from './ShowMore';

interface OverviewProps {
  gitStatus: VpApi.GetGitStatusResponse;
}

@observer
export default class Overview extends React.Component<OverviewProps, {}> {

  private static displayedListLength: number = 5;

  @observable isExpanded: boolean = false;

  onShowMoreClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.isExpanded = true;
  };

  render() {
    const { gitStatus } = this.props;

    const displayedListLength = Overview.displayedListLength;
    const lines = this.isExpanded
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
          {(gitStatus.length > displayedListLength && !this.isExpanded) &&
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
