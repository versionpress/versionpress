/// <reference path='./Commits.d.ts' />

import * as React from 'react';

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
    if (this.props.commit === null || this.props.detailsLevel === 'none') {
      return <tr />;
    }
    const commit = this.props.commit;
    const className = 'details-row' + (commit.isEnabled ? '' : 'disabled') + (this.props.isLoading ? ' loading' : '');
    const detailsClass = 'details';

    const overview = <CommitOverview commit={commit} />;

    const overviewRow = (
      <tr className={className}>
        <td />
        <td />
        <td />
        <td />
        <td>
          {this.props.isLoading
            ? <div className='details-row-loader'/>
            : null
          }
          <div className={detailsClass}>{overview}</div>
        </td>
        <td />
      </tr>
    );

    const fullDiffRow = (
      <tr className={className}>
        <td colSpan={6}>
          <div className={detailsClass}>
            <DiffPanel diff={this.props.diff} />
          </div>
        </td>
      </tr>
    );

    return this.props.detailsLevel === 'overview' ? overviewRow : fullDiffRow;
  }

}
