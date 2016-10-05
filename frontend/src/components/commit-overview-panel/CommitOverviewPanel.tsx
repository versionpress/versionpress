/// <reference path='../common/Commits.d.ts' />
/// <reference path='./CommitOverviewPanel.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';
import { observable } from 'mobx';

import preprocessLines from './preprocessLines';
import Line from './line/Line';
import Merge from './line/Merge';
import NoChanges from './line/NoChanges';
import Environment from './line/Environment';

interface CommitOverviewPanelProps {
  commit: Commit;
}

@observer
export default class CommitOverviewPanel extends React.Component<CommitOverviewPanelProps, {}> {

  @observable expandedLists: string[] = [];

  onShowMoreClick = (e: React.MouseEvent, listKey: string) => {
    e.preventDefault();

    this.expandedLists = this.expandedLists.concat([listKey]);
  };

  renderLines = (lines: PreprocessedLine[]) => {
    const { commit } = this.props;

    if (commit.isMerge) {
      return [<Merge />];
    }
    if (lines.length === 0) {
      return [<NoChanges />];
    }

    return lines.map(({key, changes}) => (
      <li key={key}>
        <Line
          changes={changes}
          expandedLists={this.expandedLists}
          onShowMoreClick={this.onShowMoreClick}
        />
      </li>
    ));
  };

  render() {
    const { commit } = this.props;

    const lines = preprocessLines(commit.changes);

    return (
      <ul className='overview-list'>
        {this.renderLines(lines)}
        <Environment environment={commit.environment} />
      </ul>
    );
  }

}
