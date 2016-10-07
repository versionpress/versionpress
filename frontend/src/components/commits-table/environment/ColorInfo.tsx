import * as React from 'react';
import { observer } from 'mobx-react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface ColorInfoProps {
  environment: string;
  onClick(e: React.MouseEvent): void;
}

const ColorInfo: React.StatelessComponent<ColorInfoProps> = ({ environment, onClick }) => (
  <div
    className='environment-info'
    style={{ backgroundColor: getGitBranchColor(environment) }}
    onClick={onClick}
  >
    {environment}
  </div>
);
export default observer(ColorInfo);
