/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import * as classNames from 'classnames';

import CommitOverview from '../overview/CommitOverview.react';
import DiffPanel from '../../common/diff-panel/DiffPanel.react';
import DetailsLevel from '../../enums/DetailsLevel';

interface CommitsTableRowDetailsProps {
  commit: Commit;
  detailsLevel: DetailsLevel;
  diff?: string;
  isLoading?: boolean;
}

export default class CommitsTableRowDetails extends React.Component<CommitsTableRowDetailsProps, {}> {

  private renderOverviewRow(className: string) {
    const { commit, isLoading } = this.props;

    return (
      <tr className={className}>
        <td />
        <td />
        <td />
        <td />
        <td>
          {isLoading
            ? <div className='details-row-loader' />
            : null
          }
          <div className='details'>
            <CommitOverview commit={commit} />
          </div>
        </td>
        <td />
      </tr>
    );
  }

  private renderFullDiffRow(className: string) {
    const { diff } = this.props;

    return (
      <tr className={className}>
        <td colSpan={6}>
          <div className='details'>
            <DiffPanel diff={diff} />
          </div>
        </td>
      </tr>
    );
  }

  render() {
    const { commit, detailsLevel, isLoading } = this.props;

    if (commit === null || detailsLevel === DetailsLevel.None) {
      return <tr />;
    }

    const rowClassName = classNames({
      'details-row': true,
      'disabled': !commit.isEnabled,
      'loading': isLoading,
    });

    return detailsLevel === DetailsLevel.Overview
      ? this.renderOverviewRow(rowClassName)
      : this.renderFullDiffRow(rowClassName);
  }

}
