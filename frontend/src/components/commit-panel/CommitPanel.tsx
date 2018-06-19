import * as React from 'react';
import * as classNames from 'classnames';
import { inject, observer } from 'mobx-react';

import Commit from './commit/Commit';
import Details from './details/Details';
import Notice from './Notice';
import DetailsLevel from '../../enums/DetailsLevel';

import { changeDetailsLevel, commit, discard } from '../../actions';
import { CommitPanelStore } from '../../stores/commitPanelStore';

import './CommitPanel.less';

interface CommitPanelProps {
  commitPanelStore?: CommitPanelStore;
}

@inject('commitPanelStore')
@observer
export default class CommitPanel extends React.Component<CommitPanelProps, {}> {

  onDetailsLevelChange = (detailsLevel: DetailsLevel) => {
    const { commitPanelStore } = this.props;
    changeDetailsLevel(detailsLevel, commitPanelStore!);
  }

  onCommit = (message: string) => {
    commit(message);
  }

  onDiscard = () => {
    discard();
  }

  render() {
    const { commitPanelStore } = this.props;
    const { detailsLevel, diff, gitStatus, error, isLoading } = commitPanelStore!;

    const noticeClassName = classNames({
      'CommitPanel-notice': true,
      'CommitPanel-notice--expanded': detailsLevel !== DetailsLevel.None,
    });

    return (
      <div className='CommitPanel'>
        <div className={noticeClassName}>
          <Notice
            onDetailsLevelChange={this.onDetailsLevelChange}
            detailsLevel={detailsLevel}
          />
          {detailsLevel !== DetailsLevel.None &&
            <Commit
              onCommit={this.onCommit}
              onDiscard={this.onDiscard}
            />
          }
        </div>
        <Details
          detailsLevel={detailsLevel}
          diff={diff}
          gitStatus={gitStatus}
          error={error}
          isLoading={isLoading}
          onDetailsLevelChange={this.onDetailsLevelChange}
        />
      </div>
    );
  }

}
