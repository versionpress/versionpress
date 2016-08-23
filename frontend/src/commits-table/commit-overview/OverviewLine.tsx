import * as React from 'react';

import * as ArrayUtils from '../../common/ArrayUtils';
import * as StringUtils from '../../common/StringUtils';

interface OverviewLineProps {
  expandedLists: string[];
  type: string;
  action: string;
  entities: any[];
  suffix: any;
}

const OverviewLine: React.StatelessComponent<OverviewLineProps> = (props) => {
  const {
    expandedLists,
    type,
    action,
    entities,
    suffix = null,
  } = props;

  const capitalizedVerb = StringUtils.capitalize(StringUtils.verbToPastTense(action));

  if (entities.length < 5) {
    return (
      <span>
        {capitalizedVerb}
        {' '} <span className='type'>{entities.length === 1 ? type : StringUtils.pluralize(type)}</span>
        {' '} {ArrayUtils.interspace(entities, ', ', ' and ')}
        {suffix}
      </span>
    );
  }

  const listKey = `${type}|||${action}|||${suffix}`;
  let entityList;
  if (expandedLists.indexOf(listKey) > -1) {
    entityList = (
      <ul>
        {entities.map(entity => <li>{entity}</li>)}
      </ul>
    );
  } else {
    let displayedListLength = 3;
    entityList = (
      <ul>
        {entities.slice(0, displayedListLength).map(entity => <li>{entity}</li>)}
        <li>
          <a onClick={e => this.onShowMoreClick(e, listKey)}>
            show {entities.length - displayedListLength} more...
          </a>
        </li>
      </ul>
    );
  }

  return (
    <span>
      {capitalizedVerb}
      {' '} <span className='type'>{StringUtils.pluralize(type)}</span>
      {suffix}
      {entityList}
    </span>
  );
};

export default OverviewLine;
