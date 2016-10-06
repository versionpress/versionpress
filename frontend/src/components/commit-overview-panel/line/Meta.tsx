/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import OverviewLine from './OverviewLine';
import { LineProps } from './Line';

interface MetaProps extends LineProps {
  parentEntity: string;
  groupByTag: string;
}

const Meta: React.StatelessComponent<MetaProps> = (props) => {
  const {
    changes,
    parentEntity,
    groupByTag,
    expandedLists,
    onShowMoreClick,
  } = props;

  const tagValue = changes[0].tags[groupByTag];
  const suffix = (
    <span>
      {' for '}
      <span className='type'>{parentEntity}</span>
      {' '}
      <span className='identifier'>{tagValue}</span>
    </span>
  );

  return (
    <OverviewLine
      expandedLists={expandedLists}
      changes={changes}
      suffix={suffix}
      onShowMoreClick={onShowMoreClick}
    />
  );
};

export default observer(Meta);
