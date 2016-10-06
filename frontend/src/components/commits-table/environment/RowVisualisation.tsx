import * as React from 'react';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface RowVisualisationProps {
  width: number;
  height: number;
  left: number;
  space: number;
  strokeWidth: number;
  dotRadius: number;
  visualisation: Visualisation;
  onClick(e: React.MouseEvent): void;
}

const RowVisualisation: React.StatelessComponent<RowVisualisationProps> = (props) => {
  const {
    width,
    height,
    left,
    space,
    strokeWidth,
    dotRadius,
    visualisation,
    onClick,
  } = props;

  return (
    <svg
      width={width}
      height={height}
      onClick={onClick}
    >
      {visualisation.upperRoutes && visualisation.upperRoutes.map(route => {
        const { from, to } = route;
        const areSame = from === to;
        const isToBigger = to > from;

        return (
          <line
            x1={left + from * space * (areSame ? 1 : (isToBigger ? 1.5 : .5)) + (
                      (!areSame && (from === 0 || !isToBigger)) ? space * to * .5 : 0)
                    }
            y1='0%'
            x2={left + to * space}
            y2='50%'
            strokeWidth={strokeWidth}
            stroke={getGitBranchColor(route.environment)}
            key={`upper-${from}-${to}`}
          />
        );
      })}
      {visualisation.lowerRoutes && visualisation.lowerRoutes.map(route => {
        const { from, to } = route;
        const areSame = from === to;
        const isFromBigger = from > to;

        return (
          <line
            x1={left + from * space}
            y1='50%'
            x2={left + to * space * (areSame ? 1 : (isFromBigger ? 1.5 : .5)) + (
                      (!areSame && (to === 0 || !isFromBigger)) ? space * from * .5 : 0)
                    }
            y2='100%'
            strokeWidth={strokeWidth}
            stroke={getGitBranchColor(route.environment)}
            key={`lower-${from}-${to}`}
          />
        );
      })}
      <circle
        cx={left + visualisation.offset * space}
        cy='50%'
        r={dotRadius}
        fill={getGitBranchColor(visualisation.environment)}
      />
    </svg>
  );
};
export default RowVisualisation;

