import * as React from 'react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface EnvironmentProps {
  environment: string;
  showVisualization: boolean;
  visualization: Visualization;
}

const LEFT = 10;
const SPACE = 15;

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
        style={{ borderBottom: 0, borderRight: '1px solid #ccc', position: 'relative' }}
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
            style={{ position: 'absolute', top: 0 }}
          >
            {visualization.upperRoutes && visualization.upperRoutes.map(route => {
              const { from, to } = route;
              const areSame = from === to;

              return (
                <line
                  x1={LEFT + from * SPACE * (areSame? 1 : .5) + ((!areSame && from === 0) ? SPACE * .5 : 0)} y1="0%"
                  x2={LEFT + to * SPACE} y2="50%"
                  strokeWidth="2"
                  stroke={getGitBranchColor(route.environment)}
                  key={`upper${route.branch}`}
                />
              );
            })}
            {visualization.lowerRoutes && visualization.lowerRoutes.map(route => {
              const { from, to } = route;
              const areSame = from === to;

              return (
                <line
                  x1={LEFT + from * SPACE} y1="50%"
                  x2={LEFT + to * SPACE * (areSame ? 1 : .5) + ((!areSame && to === 0) ? SPACE * .5 : 0)} y2="100%"
                  strokeWidth="2"
                  stroke={getGitBranchColor(route.environment)}
                  key={`lower${route.branch}`}
                />
              );
            })}
            <circle
              cx={LEFT + visualization.offset * SPACE}
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
