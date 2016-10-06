import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface EnvironmentProps {
  environment: string;
  showVisualization: boolean;
  visualization: Visualization;
  onChangeShowVisualization(): void;
}

const LEFT = 10;
const SPACE = 15;
const STROKE_WIDTH = 2;
const CIRCLE_RADIUS = 4;

@observer
export default class Environment extends React.Component<EnvironmentProps, {}> {

  private tdDom;

  componentDidMount() {
    this.forceUpdate();

    window.addEventListener('resize', () => this.forceUpdate());
  }

  onChangeShowVisualization = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();

    this.props.onChangeShowVisualization();
  };

  render() {
    const { environment, showVisualization, visualization } = this.props;

    const environmentClassName = classNames({
      'column-environment': true,
      'visualisation': showVisualization
    });

    return (
      <td
        className={environmentClassName}
        ref={tdDom => this.tdDom = tdDom}
      >
        {(!showVisualization && environment !== '?') &&
        <div
          className='environment-info'
          style={{ backgroundColor: getGitBranchColor(environment) }}
          onClick={this.onChangeShowVisualization}
        >
          {environment}
        </div>
        }
        {showVisualization &&
          <svg
            width={!this.tdDom ? 50 : this.tdDom.getBoundingClientRect().width}
            height={!this.tdDom ? 20 : this.tdDom.getBoundingClientRect().height}
            onClick={this.onChangeShowVisualization}
          >
            {visualization.upperRoutes && visualization.upperRoutes.map(route => {
              const { from, to } = route;
              const areSame = from === to;
              const isToBigger = to > from;

              return (
                <line
                  x1={LEFT + from * SPACE * (areSame ? 1 : (isToBigger ? 1.5 : .5)) + (
                    (!areSame && (from === 0 || !isToBigger)) ? SPACE * to * .5 : 0)
                  }
                  y1='0%'
                  x2={LEFT + to * SPACE}
                  y2='50%'
                  strokeWidth={STROKE_WIDTH}
                  stroke={getGitBranchColor(route.environment)}
                  key={`upper-${from}-${to}`}
                />
              );
            })}
            {visualization.lowerRoutes && visualization.lowerRoutes.map(route => {
              const { from, to } = route;
              const areSame = from === to;
              const isFromBigger = from > to;

              return (
                <line
                  x1={LEFT + from * SPACE}
                  y1='50%'
                  x2={LEFT + to * SPACE * (areSame ? 1 : (isFromBigger ? 1.5 : .5)) + (
                    (!areSame && to === 0) ? SPACE * .5 : 0)
                  }
                  y2='100%'
                  strokeWidth={STROKE_WIDTH}
                  stroke={getGitBranchColor(route.environment)}
                  key={`lower-${from}-${to}`}
                />
              );
            })}
            <circle
              cx={LEFT + visualization.offset * SPACE}
              cy='50%'
              r={CIRCLE_RADIUS}
              fill={getGitBranchColor(visualization.environment)}
            />
          </svg>
        }
        {showVisualization &&
          <div
            className='environment-detail'
            style={{
              left: LEFT + visualization.offset * SPACE + SPACE * .5,
              backgroundColor: getGitBranchColor(visualization.environment),
            }}
          >
            {environment}
          </div>
        }
      </td>
    );
  }

};
