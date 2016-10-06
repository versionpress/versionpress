import * as React from 'react';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

import ColorInfo from './ColorInfo';
import Detail from './Detail';
import RowVisualisation from './RowVisualisation';

interface EnvironmentProps {
  environment: string;
  showVisualisation: boolean;
  visualisation: Visualisation;
  onChangeShowVisualisation(): void;
}

const LEFT = 10;
const SPACE = 15;
const STROKE_WIDTH = 2;
const DOT_RADIUS = 4;

@observer
export default class Environment extends React.Component<EnvironmentProps, {}> {

  private tdDom;

  componentDidMount() {
    this.forceUpdate();

    window.addEventListener('resize', () => this.forceUpdate());
  }

  onChangeShowVisualisation = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();

    this.props.onChangeShowVisualisation();
  };

  render() {
    const { environment, showVisualisation, visualisation } = this.props;

    const environmentClassName = classNames({
      'column-environment': true,
      'visualisation': showVisualisation
    });

    return (
      <td
        className={environmentClassName}
        ref={tdDom => this.tdDom = tdDom}
      >
        {(!showVisualisation && environment !== '?') &&
          <ColorInfo
            environment={environment}
            onClick={this.onChangeShowVisualisation}
          />
        }
        {showVisualisation &&
          <RowVisualisation
            width={!this.tdDom ? 50 : this.tdDom.getBoundingClientRect().width}
            height={!this.tdDom ? 20 : this.tdDom.getBoundingClientRect().height}
            left={LEFT}
            space={SPACE}
            strokeWidth={STROKE_WIDTH}
            dotRadius={DOT_RADIUS}
            visualisation={visualisation}
            onClick={this.onChangeShowVisualisation}
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
      </td>
    );
  }

};
