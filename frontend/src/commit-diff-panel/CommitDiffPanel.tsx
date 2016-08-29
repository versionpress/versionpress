/// <reference path='../common/Diff.d.ts' />

import * as React from 'react';

import Diff from './diff/Diff';
import DiffParser from '../common/DiffParser';

import './CommitDiffPanel.less';

interface CommitDiffPanelProps {
  diff: string;
}

const CommitDiffPanel: React.StatelessComponent<CommitDiffPanelProps> = ({ diff }) => {
  if (diff === null) {
    return <div />;
  }

  const diffs = DiffParser.parse(diff);

  return (
    <div>
      {diffs.map((diff: Diff, i) => <Diff diff={diff} key={i} />)}
    </div>
  );
};

export default CommitDiffPanel;
