import * as React from 'react';

import DiffPanel from '../../commits-table/commit-full-diff/DiffPanel';

interface DiffProps {
  diff: string;
}

const Diff: React.StatelessComponent<DiffProps> = ({ diff }) => (
  <DiffPanel diff={diff} />
);

export default Diff;
