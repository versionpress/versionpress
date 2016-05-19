import * as React from 'react';
import DiffPanel from '../Commits/DiffPanel.react';

interface CommitPanelDetailsProps extends React.Props<JSX.Element> {
  diff: string;
}

export default class CommitPanelDetails extends React.Component<CommitPanelDetailsProps, {}> {

  render() {
    return <DiffPanel diff={this.props.diff} />;
  }

}
