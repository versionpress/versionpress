/// <reference path='../../typings/typings.d.ts' />
/// <reference path='./Commits.d.ts' />

import * as React from 'react';
import CommitsTableRowSummary from './CommitsTableRowSummary.react';
import CommitsTableRowDetails from './CommitsTableRowDetails.react';

interface CommitsTableRowProps extends React.Props<JSX.Element> {
  commit: Commit;
  onUndo: React.MouseEventHandler;
  onRollback: React.MouseEventHandler;
  diffProvider: {getDiff: (hash: string) => Promise<string>};
}

interface CommitsTableRowState {
  detailsLevel?: string;
  diff?: string;
  error?: string;
  loading?: boolean;
}

export default class CommitsTableRow extends React.Component<CommitsTableRowProps, CommitsTableRowState> {

  constructor() {
    super();
    this.state = {detailsLevel: 'none'};
  }

  render() {
    return (
      <tbody>
        <CommitsTableRowSummary
          commit={this.props.commit}
          onUndo={this.props.onUndo}
          onRollback={this.props.onRollback}
          onDetailsLevelChanged={detailsLevel => this.changeDetailsLevel(detailsLevel)}
          detailsLevel={this.state.detailsLevel}
        />
        {this.state.error
          ? this.renderError()
          : <CommitsTableRowDetails
              commit={this.props.commit}
              detailsLevel={this.state.detailsLevel}
              diff={this.state.diff}
              loading={this.state.loading}
            />
        }
      </tbody>
    );
  }

  renderError() {
    return (
      <tr className='details-row error'>
        <td colSpan={3}>{this.state.error}</td>
      </tr>
    );
  }

  private changeDetailsLevel(detailsLevel: string) {
    if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.setState({loading: true});
      this.props.diffProvider.getDiff(this.props.commit.hash)
        .then(diff => this.setState(
          {
            detailsLevel: detailsLevel,
            diff: diff,
            error: null,
            loading: false
          })
        ).catch(err => {
          this.setState({detailsLevel: detailsLevel, error: err.message, loading: false});
        });
    } else {
      this.setState({detailsLevel: detailsLevel, error: null, loading: false});
    }
  }
}
