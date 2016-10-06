import * as React from 'react';
import { observer } from 'mobx-react';

import CommitDiffPanel from '../../commit-diff-panel/CommitDiffPanel';

interface DiffProps {
  diff: string;
}

const Diff: React.StatelessComponent<DiffProps> = ({ diff }) => (
  <CommitDiffPanel diff={diff} />
);

export default observer(Diff);
