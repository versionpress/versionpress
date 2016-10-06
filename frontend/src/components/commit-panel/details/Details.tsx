import * as React from 'react';
import { observer } from 'mobx-react';
import * as classNames from 'classnames';

import Buttons from './Buttons';
import Diff from '../diff/Diff';
import Error from './Error';
import Loader from './Loader';
import Overview from '../overview/Overview';
import DetailsLevel from '../../../enums/DetailsLevel';

interface DetailsProps {
  detailsLevel: DetailsLevel;
  diff: string;
  gitStatus: VpApi.GetGitStatusResponse;
  error: string;
  isLoading: boolean;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

const Details: React.StatelessComponent<DetailsProps> = (props) => {
  const {
    detailsLevel,
    diff,
    gitStatus,
    error,
    isLoading,
    onDetailsLevelChange,
  } = props;

  if (!error && detailsLevel === DetailsLevel.None) {
    return <div />;
  }

  const detailsClassName = classNames({
    'CommitPanel-details': true,
    'loading': isLoading,
  });

  return (
    <div className={detailsClassName}>
      {detailsLevel !== DetailsLevel.None &&
        <Buttons
          detailsLevel={detailsLevel}
          onDetailsLevelChange={onDetailsLevelChange}
        />
      }
      {isLoading && <Loader />}
      {error
        ? <Error error={error} />
        : detailsLevel === DetailsLevel.Overview
          ? <Overview gitStatus={gitStatus} />
          : <Diff diff={diff} />
      }
    </div>
  );
};

export default observer(Details);
