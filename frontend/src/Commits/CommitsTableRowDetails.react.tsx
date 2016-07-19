/// <reference path='./Commits.d.ts' />

import * as React from 'react';
import * as classNames from 'classnames';

import CommitOverview from './CommitOverview.react';
import DiffPanel from './DiffPanel.react';

interface CommitsTableRowDetailsProps extends React.Props<JSX.Element> {
  commit: Commit;
  detailsLevel: string;
  diff?: string;
  isLoading?: boolean;
}

export default class CommitsTableRowDetails extends React.Component<CommitsTableRowDetailsProps, {}> {

  render() {
    const { commit, detailsLevel, isLoading, diff } = this.props;

    if (commit === null || detailsLevel === 'none') {
      return <tr />;
    }

    const rowClassName = classNames({
      'details-row': true,
      'disabled': !commit.isEnabled,
      'loading': isLoading
    });

    const overviewRow = (
      <tr className={rowClassName}>
        <td />
        <td />
        <td />
        <td />
        <td>
          {isLoading
            ? <div className='details-row-loader'/>
            : null
          }
          <div className='details'>
            <CommitOverview commit={commit} />
          </div>
        </td>
        <td />
      </tr>
    );

    const fullDiffRow = (
      <tr className={rowClassName}>
        <td colSpan={6}>
          <div className='details'>
            <DiffPanel diff={diff} />
          </div>
        </td>
      </tr>
    );

    return detailsLevel === 'overview' ? overviewRow : fullDiffRow;
  }

}
