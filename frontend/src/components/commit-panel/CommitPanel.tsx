import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

import Commit from './commit/Commit';
import Details from './details/Details';
import Notice from './Notice';
import DetailsLevel from '../../enums/DetailsLevel';

import store from '../../stores/commitPanelStore';

import './CommitPanel.less';

@observer
export default class CommitPanel extends React.Component<{}, {}> {

  onDetailsLevelChange = (detailsLevel: DetailsLevel) => {
    store.changeDetailsLevel(detailsLevel);
  };

  onCommit = (message: string) => {
    store.commit(message);
  };

  onDiscard = () => {
    store.discard();
  };

  render() {
    const { detailsLevel } = store;

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
          {...store}
          onDetailsLevelChange={this.onDetailsLevelChange}
        />
      </div>
    );
  }

}
