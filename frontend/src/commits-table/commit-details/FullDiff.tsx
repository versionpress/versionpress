import * as React from 'react';

import DiffPanel from '../commit-full-diff/DiffPanel';

interface FullDiffProps {
  diff: string;
  className: string;
}

const FullDiff: React.StatelessComponent<FullDiffProps> = ({ diff, className }) => (
  <tr className={className}>
    <td colSpan={6}>
      <div className='details'>
        <DiffPanel diff={diff} />
      </div>
    </td>
  </tr>
);

export default FullDiff;
