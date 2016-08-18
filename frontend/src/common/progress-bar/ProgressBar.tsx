import * as React from 'react';

import Spinner from './Spinner';
import SpinnerIcon from './SpinnerIcon';

import './ProgressBar.less';

interface ProgressBarState {
  isVisible?: boolean;
  progress?: number;
}

export default class ProgressBar extends React.Component<React.Props<JSX.Element>, ProgressBarState> {

  state = {
    isVisible: false,
    progress: 0,
  };

  progress(progress: number) {
    this.setState({
      progress,
      isVisible: progress < 100,
    });
  }

  render() {
    const { isVisible, progress } = this.state;

    const barStyles = {
      transform: `translate3d(${progress - 100}%,0px,0px)`,
      display: (isVisible ? 'inline-block' : 'none'),
    };

    return (
      <div className='ProgressBar'>
        <div
          className='ProgressBar-bar'
          style={barStyles}
        >
          <Spinner isVisible={isVisible} />
          <SpinnerIcon />
        </div>
      </div>
    );
  }

}
