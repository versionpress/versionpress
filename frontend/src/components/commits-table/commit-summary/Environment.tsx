import * as React from 'react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface EnvironmentProps {
  environment: string;
  showVisualization: boolean;
  visualization: Visualization;
}

export default class Environment extends React.Component<EnvironmentProps, {}> {

  private tdDom;

  componentDidMount() {
    this.forceUpdate();
  }

  render() {
    const { environment, showVisualization, visualization } = this.props;

    return (
      <td
        className='column-environment'
        ref={tdDom => this.tdDom = tdDom}
      >
        {(!showVisualization && environment !== '?') &&
        <div style={{ backgroundColor: getGitBranchColor(environment) }}>
          {environment}
        </div>
        }
        {showVisualization &&
          <svg
            width={!this.tdDom ? 50 : this.tdDom.getBoundingClientRect().width}
            height={!this.tdDom ? 20 : this.tdDom.getBoundingClientRect().height}
          >
            <circle
              cx={10 + visualization.offset * 10}
              cy="50%"
              r="4"
              fill={getGitBranchColor(visualization.environment)}
            />
          </svg>
        }
      </td>
    );
  }

};
