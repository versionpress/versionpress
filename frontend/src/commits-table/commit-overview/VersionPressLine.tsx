import * as React from 'react';

import * as StringUtils from '../../common/StringUtils';

interface VersionPressLineProps {
  action: string;
}

const VersionPressLine: React.StatelessComponent<VersionPressLineProps> = ({ action }) => (
  <span>
    {StringUtils.capitalize(StringUtils.verbToPastTense(action))}
    {' '}
    <span className='identifier'>VersionPress</span>
  </span>
);

export default VersionPressLine;
