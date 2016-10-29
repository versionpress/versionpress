import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

import ColorInfo from './ColorInfo';
import Detail from './Detail';
import RowVisualisation from './RowVisualisation';

interface EnvironmentProps {
  environment: string;
  branches: number;
  showVisualisation: boolean;
  visualisation: Visualisation;
  onToggleShowVisualisation(): void;
}

const LEFT = 10;
const SPACE = 15;
const STROKE_WIDTH = 2;
const DOT_RADIUS = 4;

@observer
export default class Environment extends React.Component<EnvironmentProps, {}> {

  tableCellNode: HTMLDivElement = null;

  componentDidMount() {
    this.forceUpdate();

    window.addEventListener('resize', this.onResize);
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.onResize)
  }

  onResize = () => {
    this.forceUpdate();
  };

  render() {
    const {
      environment,
      branches,
      showVisualisation,
      visualisation,
      onToggleShowVisualisation,
    } = this.props;

    const environmentClassName = classNames({
      'column-environment': true,
      'visualisation': showVisualisation,
    });

    return (
      <div
        className={environmentClassName}
        style={{ flex: `0 0 ${showVisualisation ? branches * 20 : 20}px` }}
        ref={node => this.tableCellNode = node}
      >
        {(!showVisualisation && environment !== '?') &&
          <ColorInfo
            environment={environment}
            onToggleShowVisualisation={onToggleShowVisualisation}
          />
        }
        {showVisualisation &&
          <RowVisualisation
            width={!this.tableCellNode ? 50 : showVisualisation ? branches * 20 : 20}
            height={!this.tableCellNode ? 20 : this.tableCellNode.getBoundingClientRect().height}
            left={LEFT}
            space={SPACE}
            strokeWidth={STROKE_WIDTH}
            dotRadius={DOT_RADIUS}
            visualisation={visualisation}
            onToggleShowVisualisation={onToggleShowVisualisation}
          />
        }
        {showVisualisation &&
          <Detail
            environment={environment}
            left={LEFT}
            space={SPACE}
            offset={visualisation.offset}
          />
        }
      </div>
    );
  }

};
