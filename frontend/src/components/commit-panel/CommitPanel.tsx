import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

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

@observer(['commitPanelStore'])
export default class CommitPanel extends React.Component<CommitPanelProps, {}> {

  onDetailsLevelChange = (detailsLevel: DetailsLevel) => {
    const { commitPanelStore } = this.props;
    changeDetailsLevel(detailsLevel, commitPanelStore);
  }

  onCommit = (message: string) => {
    commit(message);
  }

  onDiscard = () => {
    discard();
  }

  render() {
    const { commitPanelStore } = this.props;
    const { detailsLevel } = commitPanelStore;

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
          detailsLevel={commitPanelStore.detailsLevel}
          diff={commitPanelStore.diff}
          gitStatus={commitPanelStore.gitStatus}
          error={commitPanelStore.error}
          isLoading={commitPanelStore.isLoading}
          onDetailsLevelChange={this.onDetailsLevelChange}
        />
      </div>
    );
  }

}
