import * as React from 'react';

import DiffPanel from '../../Commits/DiffPanel.react';

interface DiffProps {
  diff: string;
}

const Diff: React.StatelessComponent<DiffProps> = ({ diff }) => (
  <DiffPanel diff={diff} />
);

export default Diff;
