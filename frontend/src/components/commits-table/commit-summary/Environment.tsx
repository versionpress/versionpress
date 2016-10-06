import * as React from 'react';
import { observer } from 'mobx-react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface EnvironmentProps {
  environment: string;
}

const Environment: React.StatelessComponent<EnvironmentProps> = ({ environment }) => (
  <td className='column-environment'>
    {environment !== '?' &&
      <div style={{ backgroundColor: getGitBranchColor(environment) }}>
        {environment}
      </div>
    }
  </td>
);

export default observer(Environment);
