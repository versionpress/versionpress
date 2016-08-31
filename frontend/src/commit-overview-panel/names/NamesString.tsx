/// <reference path='../../common/Commits.d.ts' />
/// <reference path='../CommitOverviewPanel.d.ts' />

import * as React from 'react';

import renderNames from './renderNames';
import * as ArrayUtils from '../../utils/ArrayUtils';

interface NamesStringProps {
  filteredChanges: Change[];
  countOfDuplicates: CountOfDuplicateChanges;
}

const NamesString: React.StatelessComponent<NamesStringProps> = ({ filteredChanges, countOfDuplicates }) => {
  const names = renderNames(filteredChanges, countOfDuplicates);

  return (
    <span>
      {ArrayUtils.interspace(names, ', ', ' and ')}
    </span>
  );
};

export default NamesString;
