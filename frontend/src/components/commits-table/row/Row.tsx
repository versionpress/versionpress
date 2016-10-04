/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';

import CommitDetails from '../commit-details/CommitDetails';
import CommitSummary from '../commit-summary/CommitSummary';
import Error from './Error';
import DetailsLevel from '../../../enums/DetailsLevel';

interface RowProps {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  diffProvider: {getDiff(hash: string): Promise<string>};
  onUndo(hash: string, message: string): void;
  onRollback(hash: string, date: string): void;
  onCommitsSelect(commits: Commit[], isChecked: boolean, isShiftKey: boolean): void;
}

interface RowState {
  detailsLevel?: DetailsLevel;
  diff?: string;
  error?: string;
  isLoading?: boolean;
}

export default class Row extends React.Component<RowProps, RowState> {

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
      this.setLoading();

      diffProvider.getDiff(commit.hash)
        .then(this.handleSuccess(detailsLevel))
        .catch(this.handleError(detailsLevel));
      return;
    }

    this.setState({
      detailsLevel: detailsLevel,
      error: null,
      isLoading: false,
    });
  };

  private setLoading = () => {
    this.setState({
      isLoading: true,
    });
  };

  private handleSuccess = (detailsLevel: DetailsLevel) => {
    if (detailsLevel === DetailsLevel.FullDiff) {
      return diff => this.setState({
        detailsLevel: detailsLevel,
        diff: diff,
        error: null,
        isLoading: false,
      });
    }
  };

  private handleError = (detailsLevel: DetailsLevel) => {
    return err => this.setState({
      detailsLevel: detailsLevel,
      error: err.message,
      isLoading: false,
    });
  };

  render() {
    const { commit } = this.props;
    const { detailsLevel, diff, error, isLoading } = this.state;

    return (
      <tbody>
        <CommitSummary
          detailsLevel={detailsLevel}
          onDetailsLevelChange={this.onDetailsLevelChange}
          {...this.props}
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
