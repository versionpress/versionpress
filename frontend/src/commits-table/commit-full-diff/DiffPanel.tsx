/// <reference path='../../common/Diff.d.ts' />

import * as React from 'react';

import Panel from './Panel';
import DiffParser from '../../common/DiffParser';

import './DiffPanel.less';

interface DiffPanelProps {
  diff: string;
}

const DiffPanel: React.StatelessComponent<DiffPanelProps> = ({ diff }) => {
  if (diff === null) {
    return <div />;
  }

  const diffs = DiffParser.parse(diff);

  return (
    <div>
      {diffs.map((diff: Diff, i) => (
        <Panel
          diff={diff}
          key={i}
        />
      ))}
    </div>
  );
};

export default DiffPanel;
