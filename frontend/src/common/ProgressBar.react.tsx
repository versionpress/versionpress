import * as React from 'react';

import './ProgressBar.less';

interface ProgressBarState {
  isVisible?: boolean;
  progress?: number;
}

export default class ProgressBar extends React.Component<React.Props<JSX.Element>, ProgressBarState> {

  constructor() {
    super();
    this.state = {
      isVisible: false,
      progress: 0,
    };
  }

  progress(progress: number) {
    this.setState({
      progress: progress,
      isVisible: progress < 100,
    });
  }

  render() {
    const { isVisible, progress } = this.state;

    const styles = {
      transform: `translate3d(${progress - 100}%,0px,0px)`,
      display: (isVisible ? 'inline-block' : 'none'),
    };

    return (
      <div className='ProgressBar'>
        <div className='ProgressBar-bar' style={styles}>
          <div className={'ProgressBar-spinner' + (isVisible ? '' : ' hide')}></div>
          <div className='ProgressBar-spinner-icon'></div>
        </div>
      </div>
    );
  }

}
