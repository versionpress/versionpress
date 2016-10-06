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
    return <tr />;
  }

  const rowClassName = classNames({
    'details-row': true,
    'disabled': !commit.isEnabled,
    'loading': isLoading,
  });

  return detailsLevel === DetailsLevel.Overview
    ? <Overview
        commit={commit}
        className={rowClassName}
        isLoading={isLoading}
      />
    : <FullDiff
        diff={diff}
        className={rowClassName}
      />;
};

export default observer(CommitDetails);
