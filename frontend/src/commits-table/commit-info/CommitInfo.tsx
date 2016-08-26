/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';

import CommitDetails from '../commit-details/CommitDetails';
import CommitRowSummary from '../commit-row-summary/CommitRowSummary';
import Error from './Error';
import DetailsLevel from '../../enums/DetailsLevel';

interface CommitInfoProps {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  diffProvider: {getDiff(hash: string): Promise<string>};
  onUndo(hash: string, message: string): void;
  onRollback(hash: string, date: string): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
}

interface CommitInfoState {
  detailsLevel?: DetailsLevel;
  diff?: string;
  error?: string;
  isLoading?: boolean;
}

export default class CommitInfo extends React.Component<CommitInfoProps, CommitInfoState> {

  state = {
    detailsLevel: DetailsLevel.None,
    diff: null,
    error: null,
    isLoading: false,
  };

  onDetailsLevelChange = (detailsLevel: DetailsLevel) => {
    const { diffProvider, commit } = this.props;
    const { diff } = this.state;

    if (detailsLevel === DetailsLevel.FullDiff && !diff) {
      this.setState({
        isLoading: true,
      });

      diffProvider.getDiff(commit.hash)
        .then(diff => this.setState({
            detailsLevel: detailsLevel,
            diff: diff,
            error: null,
            isLoading: false,
          }))
        .catch(err => this.setState({
            detailsLevel: detailsLevel,
            error: err.message,
            isLoading: false,
          })
        );
    } else {
      this.setState({
        detailsLevel: detailsLevel,
        error: null,
        isLoading: false,
      });
    }
  };

  render() {
    const {
      commit,
      enableActions,
      isSelected,
      onUndo,
      onRollback,
      onCommitsSelect,
    } = this.props;
    const {
      detailsLevel,
      diff,
      error,
      isLoading,
    } = this.state;

    return (
      <tbody>
        <CommitRowSummary
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
