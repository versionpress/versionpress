import * as React from 'react';
import * as classNames from 'classnames';

import { DetailsLevel } from '../../enums/enums';

import Diff from './Diff';
import Overview from './Overview';

interface DetailsProps {
  detailsLevel: DetailsLevel;
  diff: string;
  gitStatus: string[][];
  error: string;
  isLoading: boolean;
  onDetailsLevelChange(detailsLevel: DetailsLevel): void;
}

export default class Details extends React.Component<DetailsProps, {}> {

  private renderError() {
    return (
      <div className='CommitPanel-error'>
        <p>{this.props.error}</p>
      </div>
    );
  }

  private renderToggle() {
    const { detailsLevel, onDetailsLevelChange } = this.props;

    if (detailsLevel === DetailsLevel.None) {
      return null;
    }

    return (
      <div className='CommitPanel-details-buttons'>
        <button
          className='button'
          disabled={detailsLevel === DetailsLevel.Overview}
          onClick={() => onDetailsLevelChange(DetailsLevel.Overview)}
        >Overview</button>
        <button
          className='button'
          disabled={detailsLevel === DetailsLevel.FullDiff}
          onClick={() => onDetailsLevelChange(DetailsLevel.FullDiff)}
        >Full diff</button>
      </div>
    );
  }

  render() {
    const { detailsLevel, diff, gitStatus, error, isLoading } = this.props;

    if (!error && detailsLevel === DetailsLevel.None) {
      return null;
    }

    const detailsClassName = classNames({
      'CommitPanel-details': true,
      'loading': isLoading,
    });
    const content = detailsLevel === DetailsLevel.Overview
      ? <Overview gitStatus={gitStatus} />
      : <Diff diff={diff} />;

    return (
      <div className={detailsClassName}>
        {this.renderToggle()}
        {isLoading
          ? <div className='CommitPanel-details-loader'></div>
          : null
        }
        {error
          ? this.renderError()
          : content
        }
      </div>
    );
  }

}
