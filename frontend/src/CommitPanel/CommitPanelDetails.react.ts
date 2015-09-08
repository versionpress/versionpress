/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import DiffPanel = require('../Commits/DiffPanel.react');

const DOM = React.DOM;

interface CommitPanelDetailsProps {
  diff: string;
}

class CommitPanelDetails extends React.Component<CommitPanelDetailsProps, {}> {

  render() {
    return React.createElement(DiffPanel, <DiffPanel.Props>{diff: this.props.diff});
  }

}

module CommitPanelDetails {
  export interface Props extends CommitPanelDetailsProps {}
}

export = CommitPanelDetails;
