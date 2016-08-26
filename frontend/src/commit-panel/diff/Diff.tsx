import * as React from 'react';

import CommitDiffPanel from '../../commits-table/commit-diff-panel/CommitDiffPanel';

interface DiffProps {
  diff: string;
}

const Diff: React.StatelessComponent<DiffProps> = ({ diff }) => (
  <CommitDiffPanel diff={diff} />
);

export default Diff;
