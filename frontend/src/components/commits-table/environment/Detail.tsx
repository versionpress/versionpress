import * as React from 'react';
import { observer } from 'mobx-react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface DetailProps {
  environment: string;
  left: number;
  space: number;
  offset: number;
}

const Detail: React.StatelessComponent<DetailProps> = ({ environment, left, space, offset }) => (
  <div
    className='environment-detail'
    style={{
      left: left + offset * space + space * .5,
      backgroundColor: getGitBranchColor(environment),
    }}
  >
    {environment}
  </div>
);
export default observer(Detail);
