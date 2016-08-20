import * as React from 'react';

import DiffPanel from '../../common/diff-panel/DiffPanel.react';

interface DiffProps {
  diff: string;
}

const Diff: React.StatelessComponent<DiffProps> = ({ diff }) => (
  <DiffPanel diff={diff} />
);

export default Diff;
