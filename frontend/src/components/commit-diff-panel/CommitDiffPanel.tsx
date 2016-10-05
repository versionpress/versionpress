/// <reference path='../common/Diff.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import Diff from './diff/Diff';
import DiffParser from '../common/DiffParser';

import './CommitDiffPanel.less';

interface CommitDiffPanelProps {
  diff: string;
}

const CommitDiffPanel: React.StatelessComponent<CommitDiffPanelProps> = ({ diff }) => (
  <div>
    {diff !== null &&
      DiffParser.parse(diff).map((diff: Diff, i) => (
        <Diff diff={diff} key={i} />
      ))
    }
  </div>
);

export default observer(CommitDiffPanel);
