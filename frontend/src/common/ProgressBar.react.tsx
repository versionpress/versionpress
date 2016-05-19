import * as React from 'react';

import './ProgressBar.less';

interface ProgressBarState {
  display?: boolean;
  progress?: number;
}

export default class ProgressBar extends React.Component<React.Props<JSX.Element>, ProgressBarState> {

  constructor() {
    super();
    this.state = {
      display: false,
      progress: 0,
    };
  }

  progress(progress: number) {
    this.setState({
      progress: progress,
      display: progress < 100,
    });
  }

  render() {
    const styles = {
      transform: `translate3d(${this.state.progress - 100}%,0px,0px)`,
      display: (this.state.display ? 'inline-block' : 'none'),
    };
    const className = 'ProgressBar';
    return (
      <div className={className}>
        <div className='ProgressBar-bar' style={styles}>
          <div className={'ProgressBar-spinner' + (this.state.display ? '' : ' hide')}></div>
          <div className='ProgressBar-spinner-icon'></div>
        </div>
      </div>
    );
  }

}
