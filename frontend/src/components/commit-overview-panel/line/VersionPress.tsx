import * as React from 'react';

import * as StringUtils from '../../../utils/StringUtils';

interface VersionPressProps {
  action: string;
}

const VersionPress: React.StatelessComponent<VersionPressProps> = ({ action }) => (
  <span>
    {StringUtils.capitalize(StringUtils.verbToPastTense(action))}
    {' '}
    <span className='identifier'>VersionPress</span>
  </span>
);

export default VersionPress;
