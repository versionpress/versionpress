/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';
import * as classNames from 'classnames';

import FullDiff from './FullDiff';
import Overview from './Overview';
import DetailsLevel from '../../../enums/DetailsLevel';

interface CommitDetailsProps {
  commit: Commit;
  detailsLevel: DetailsLevel;
  diff?: string;
  isLoading?: boolean;
}

const CommitDetails: React.StatelessComponent<CommitDetailsProps> = (props) => {
  const {
    commit,
    detailsLevel,
    diff,
    isLoading,
  } = props;

  if (commit === null || detailsLevel === DetailsLevel.None) {
    return <div />;
  }

  const rowClassName = classNames({
    'details-row': true,
    'disabled': !commit.isEnabled,
    'loading': isLoading,
  });

  const detailsClassName = classNames({
    'details': true,
    'overview-detail': detailsLevel === DetailsLevel.Overview
  });

  return (
    <div style={{ flex: '1 100%', display: 'flex', flexFlow: 'row wrap'}}>
      <div className={rowClassName}>
        {isLoading &&
          <div className="details-loader-wrap" style={{ flex: '100%', display: 'flex'}}>
            <div style={{ margin: 'auto' }} className='details-row-loader' />
          </div>
        }
        <div className={detailsClassName} style={{ flex: '100%', display: 'flex'}}>
          {detailsLevel === DetailsLevel.Overview
            ? <div style={{ flex: '100%', display: 'flex', flexFlow: 'row nowrap'}}>
                <div style={{ flex: '0 0 223px'}} />
                <div style={{ flex: '0 0 auto'}}><Overview commit={commit} /></div>
              </div>
            : <FullDiff diff={diff} />
          }
        </div>
      </div>
    </div>
  );
};

export default observer(CommitDetails);
