/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

require('./ProgressBar.less');

const DOM = React.DOM;

interface ProgressBarState {
  display?: boolean;
  progress?: number;
}

class ProgressBar extends React.Component<any, ProgressBarState> {

  constructor() {
    super();
    this.state = {
      display: false,
      progress: 0
    };
  }

  progress(progress: number) {
    this.setState({
      progress: progress,
      display: progress < 100
    });
  }

  render() {
    const styles = {
      transform: `translate3d(${this.state.progress - 100}%,0px,0px)`,
      display: (this.state.display ? 'inline-block' : 'none')
    };
    const className = 'ProgressBar';
    return DOM.div({className: className},
      DOM.div({className: 'ProgressBar-bar', style: styles}),
      DOM.div({className: 'ProgressBar-spinner' + (this.state.display ? '' : ' hide')},
        DOM.div({className: 'ProgressBar-spinner-icon'})
      )
    );
  }

}

export = ProgressBar;
