import * as React from 'react';

import CommitOverview from '../commit-overview/CommitOverview.react';

interface OverviewProps {
  commit: Commit;
  className: string;
  isLoading: boolean;
}

const Overview: React.StatelessComponent<OverviewProps> = ({ commit, className, isLoading }) => (
  <tr className={className}>
    <td colSpan={4} />
    <td colSpan={2}>
      {isLoading &&
        <div className='details-row-loader' />
      }
      <div className='details'>
        <CommitOverview commit={commit} />
      </div>
    </td>
  </tr>
);

export default Overview;
