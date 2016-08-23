/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';

import Error from './Error';
import CommitsTableRowSummary from '../row-summary/CommitsTableRowSummary.react';
import CommitsTableRowDetails from '../row-details/CommitsTableRowDetails.react';

interface CommitInfoProps extends React.Props<JSX.Element> {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  diffProvider: {getDiff(hash: string): Promise<string>};
  onUndo(e): void;
  onRollback(e): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, shiftKey: boolean): void;
}

interface CommitInfoState {
  detailsLevel?: string;
  diff?: string;
  error?: string;
  isLoading?: boolean;
}

export default class CommitInfo extends React.Component<CommitInfoProps, CommitInfoState> {

  state = {
    detailsLevel: 'none',
    diff: null,
    error: null,
    isLoading: false,
  };

  onDetailsLevelChange = (detailsLevel: string) => {
    const { diffProvider, commit } = this.props;
    const { diff } = this.state;

    if (detailsLevel === 'full-diff' && !diff) {
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
        <CommitsTableRowSummary
          commit={commit}
          enableActions={enableActions}
          isSelected={isSelected}
          onUndo={onUndo}
          onRollback={onRollback}
          onCommitsSelect={onCommitsSelect}
          onDetailsLevelChanged={this.onDetailsLevelChange}
          detailsLevel={detailsLevel}
        />
        {error
          ? <Error message={error} />
          : <CommitsTableRowDetails
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
