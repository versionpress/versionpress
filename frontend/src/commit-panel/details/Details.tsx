import * as React from 'react';
import * as classNames from 'classnames';

import { DetailsLevel } from '../../enums/enums';

import ToggleButtons from './ToggleButtons';
import DiffTab from './DiffTab';
import OverviewTab from './OverviewTab';
import Loader from './Loader';
import Error from './Error';

interface DetailsProps {
  detailsLevel: DetailsLevel;
  diff: string;
  gitStatus: VpApi.GetGitStatusResponse;
  error: string;
  isLoading: boolean;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

export default class Details extends React.Component<DetailsProps, {}> {

  render() {
    const {
      detailsLevel,
      diff,
      gitStatus,
      error,
      isLoading,
      onDetailsLevelChange,
    } = this.props;

    if (!error && detailsLevel === DetailsLevel.None) {
      return null;
    }

    const detailsClassName = classNames({
      'CommitPanel-details': true,
      'loading': isLoading,
    });

    return (
      <div className={detailsClassName}>
        <ToggleButtons
          detailsLevel={detailsLevel}
          onDetailsLevelChange={onDetailsLevelChange}
        />
        {isLoading && <Loader />}
        {error
          ? <Error error={error} />
          : detailsLevel === DetailsLevel.Overview
            ? <OverviewTab gitStatus={gitStatus} />
            : <DiffTab diff={diff} />
        }
      </div>
    );
  }

}
