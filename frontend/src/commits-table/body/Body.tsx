/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';

import CommitsTableRowSummary from '../row-summary/CommitsTableRowSummary.react';
import CommitsTableRowDetails from '../row-details/CommitsTableRowDetails.react';

interface BodyProps extends React.Props<JSX.Element> {
  commit: Commit;
  enableActions: boolean;
  isSelected: boolean;
  diffProvider: {getDiff(hash: string): Promise<string>};
  onUndo(e): void;
  onRollback(e): void;
  onCommitSelect(commits: Commit[], isChecked: boolean, shiftKey: boolean): void;
}

interface BodyState {
  detailsLevel?: string;
  diff?: string;
  error?: string;
  isLoading?: boolean;
}

export default class Body extends React.Component<BodyProps, BodyState> {

  state = {
    detailsLevel: 'none',
    diff: null,
    error: null,
    isLoading: false,
  };

  onDetailsLevelChange = (detailsLevel: string) => {
    if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.setState({
        isLoading: true,
      });

      this.props.diffProvider.getDiff(this.props.commit.hash)
        .then(diff => this.setState({
            detailsLevel: detailsLevel,
            diff: diff,
            error: null,
            isLoading: false,
          })
        ).catch(err => this.setState({
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

  renderError() {
    return (
      <tr className='details-row error'>
        <td colSpan={6}>{this.state.error}</td>
      </tr>
    );
  }

  render() {
    return (
      <tbody>
        <CommitsTableRowSummary
          commit={this.props.commit}
          enableActions={this.props.enableActions}
          isSelected={this.props.isSelected}
          onUndo={this.props.onUndo}
          onRollback={this.props.onRollback}
          onCommitSelect={this.props.onCommitSelect}
          onDetailsLevelChanged={this.onDetailsLevelChange}
          detailsLevel={this.state.detailsLevel}
        />
        {this.state.error
          ? this.renderError()
          : <CommitsTableRowDetails
              commit={this.props.commit}
              detailsLevel={this.state.detailsLevel}
              diff={this.state.diff}
              isLoading={this.state.isLoading}
            />
        }
      </tbody>
    );
  }

}
