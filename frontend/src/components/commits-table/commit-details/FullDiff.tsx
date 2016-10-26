import * as React from 'react';
import { observer } from 'mobx-react';

import CommitDiffPanel from '../../commit-diff-panel/CommitDiffPanel';

interface FullDiffProps {
  diff: string;
}

const FullDiff: React.StatelessComponent<FullDiffProps> = ({ diff }) => (
  <CommitDiffPanel diff={diff} />
);

export default observer(FullDiff);
