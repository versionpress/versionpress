import * as React from 'react';

import CommitDiffPanel from '../../commit-diff-panel/CommitDiffPanel';

interface FullDiffProps {
  diff: string;
  className: string;
}

const FullDiff: React.StatelessComponent<FullDiffProps> = ({ diff, className }) => (
  <tr className={className}>
    <td colSpan={6}>
      <div className='details'>
        <CommitDiffPanel diff={diff} />
      </div>
    </td>
  </tr>
);

export default FullDiff;
