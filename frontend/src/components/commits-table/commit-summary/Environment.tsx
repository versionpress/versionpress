import * as React from 'react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface EnvironmentProps {
  environment: string;
  visualization: Visualization;
}

const Environment: React.StatelessComponent<EnvironmentProps> = ({ environment, visualization }) => (
  <td className='column-environment'>
    {(!visualization && environment !== '?') &&
      <div style={{ backgroundColor: getGitBranchColor(environment) }}>
        {environment}
      </div>
    }
    {console.log("upper")}
    {visualization.upper ? visualization.upper.routes.forEach(route => console.log(route.from, route.to)) : "vrch"}

    {console.log("lower")}
    {visualization.lower? visualization.lower.routes.forEach(route => console.log(route.from, route.to)) : "spodek"}
    {console.log("=====")}
    </td>
);

export default Environment;
