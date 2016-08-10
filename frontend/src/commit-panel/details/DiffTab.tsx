import * as React from 'react';

import DiffPanel from '../../Commits/DiffPanel.react';

interface DiffTabProps {
  diff: string;
}

const DiffTab: React.StatelessComponent<DiffTabProps> = ({ diff }) => (
  <DiffPanel diff={diff} />
);

export default DiffTab;
