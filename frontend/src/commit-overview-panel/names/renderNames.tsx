/// <reference path='../../common/Commits.d.ts' />
/// <reference path='../CommitOverviewPanel.d.ts' />

import * as React from 'react';

import Name from './Name';

export default (filteredChanges: Change[], countOfDuplicates: CountOfDuplicateChanges) => {
  return filteredChanges.map((change: Change) => (
    <Name
      change={change}
      countOfDuplicates={countOfDuplicates}
      key={change.name}
    />
  ));
}
