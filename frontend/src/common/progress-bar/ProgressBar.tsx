import * as React from 'react';
import * as classNames from 'classnames';

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
    
    const styles = {
      transform: `translate3d(${progress - 100}%,0px,0px)`,
      display: (isVisible ? 'inline-block' : 'none'),
    };

    const spinnerClassName = classNames({
      'ProgressBar-spinner': true,
      'hide': !isVisible,
    });

    return (
      <div className='ProgressBar'>
        <div className='ProgressBar-bar' style={styles}>
          <div className={spinnerClassName}></div>
          <div className='ProgressBar-spinner-icon'></div>
        </div>
      </div>
    );
  }

}
