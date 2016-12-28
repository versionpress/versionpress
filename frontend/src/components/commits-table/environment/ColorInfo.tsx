import * as React from 'react';
import { observer } from 'mobx-react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface ColorInfoProps {
  environment: string;
  onToggleShowVisualisation(): void;
}

const ColorInfo: React.StatelessComponent<ColorInfoProps> = ({ environment, onToggleShowVisualisation }) => (
  <div
    className='environment-info'
    style={{ backgroundColor: getGitBranchColor(environment) }}
    onClick={e => { e.preventDefault(); e.stopPropagation(); onToggleShowVisualisation(); }}
  >
    {environment}
  </div>
);
export default observer(ColorInfo);
