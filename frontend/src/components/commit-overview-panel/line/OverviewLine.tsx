/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import NamesString from '../names/NamesString';
import NamesList from '../names/NamesList';
import * as ArrayUtils from '../../../utils/ArrayUtils';
import * as StringUtils from '../../../utils/StringUtils';

interface OverviewLineProps {
  expandedLists: string[];
  changes: Change[];
  type?: string;
  action?: string;
  suffix?: React.ReactNode;
  onShowMoreClick(listKey: string): void;
}

const OverviewLine: React.StatelessComponent<OverviewLineProps> = (props) => {
  const {
    expandedLists,
    changes,
    type = props.changes[0].type,
    action = props.changes[0].action,
    suffix = null,
    onShowMoreClick,
  } = props;

  const filteredChanges = getFilteredChanges(changes);
  const countOfDuplicates = getCountOfDuplicates(changes);

  const actionVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));
  const count = filteredChanges.length;

  const typeString = count === 1
    ? type
    : StringUtils.pluralize(type);

  return (
    <span>
      {actionVerb}
      {' '} <span className='type'>{typeString}</span> {' '}
      {count < 5 &&
        <NamesString
          filteredChanges={filteredChanges}
          countOfDuplicates={countOfDuplicates}
        />
      }
      {suffix}
      {count >= 5 &&
        <NamesList
          filteredChanges={filteredChanges}
          countOfDuplicates={countOfDuplicates}
          expandedLists={expandedLists}
          onShowMoreClick={onShowMoreClick}
        />
      }
    </span>
  );
};

function getFilteredChanges(changes: Change[]): Change[] {
  return ArrayUtils.filterDuplicates(
    changes,
    change => change.type + '|||' + change.action + '|||' + change.name
  ) as Change[];
}

function getCountOfDuplicates(changes: Change[]): CountOfDuplicateChanges {
  return ArrayUtils.countDuplicates(
    changes,
    change => [change.type, change.action, change.name]
  ) as CountOfDuplicateChanges;
}

export default observer(OverviewLine);
