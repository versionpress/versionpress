import * as React from 'react';
import { observer } from 'mobx-react';

import DetailsLevel from '../../enums/DetailsLevel';

interface CommitPanelNoticeProps {
  detailsLevel: DetailsLevel;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

@observer
export default class Notice extends React.Component<CommitPanelNoticeProps, {}> {

  onDetailsClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
    e.preventDefault();

    const { detailsLevel, onDetailsLevelChange } = this.props;
    onDetailsLevelChange(detailsLevel === DetailsLevel.None ? DetailsLevel.Overview : DetailsLevel.None);
  };

  render() {
    const { detailsLevel } = this.props;

    return (
      <p>
        {`You have `}
        <a
          href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files'
          target='_blank'
        >
          uncommitted changes
        </a>
        {` in your WordPress directory. `}
        <a
          className='CommitPanel-notice-toggle'
          onClick={this.onDetailsClick}
        >
          {`Click here to ${detailsLevel === DetailsLevel.None ? 'show' : 'hide'} changes.`}
        </a>
      </p>
    );
  }

}
