/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import CommitDetails from '../commit-details/CommitDetails';
import CommitSummary from '../commit-summary/CommitSummary';
import Error from './Error';
import DetailsLevel from '../../../enums/DetailsLevel';

import CommitRow from '../../../stores/CommitRow';

interface RowProps {
  commitRow: CommitRow;
  enableActions: boolean;
  onUndo(hash: string, message: string): void;
  onRollback(hash: string, date: string): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
}

@observer
export default class Row extends React.Component<RowProps, {}> {

  onDetailsLevelChange = (detailsLevel: DetailsLevel) => {
    this.props.commitRow.changeDetailsLevel(detailsLevel);
  };

  render() {
    const { commitRow, enableActions, onUndo, onRollback, onCommitsSelect } = this.props;
    const { commit, isSelected, detailsLevel, diff, error, isLoading } = commitRow;

    return (
      <tbody>
        <CommitSummary
          commit={commit}
          enableActions={enableActions}
          isSelected={isSelected}
          detailsLevel={detailsLevel}
          onUndo={onUndo}
          onRollback={onRollback}
          onCommitsSelect={onCommitsSelect}
          onDetailsLevelChange={this.onDetailsLevelChange}
        />
        {error
          ? <Error message={error} />
          : <CommitDetails
              commit={commit}
              detailsLevel={detailsLevel}
              diff={diff}
              isLoading={isLoading}
            />
        }
      </tbody>
    );
  }

}
