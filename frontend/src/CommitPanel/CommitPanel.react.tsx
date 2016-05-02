/// <reference path='../../typings/browser.d.ts' />

import * as React from 'react';

import CommitPanelCommit from './CommitPanelCommit.react';
import CommitPanelNotice from './CommitPanelNotice.react';
import CommitPanelDetails from './CommitPanelDetails.react';
import CommitPanelOverview from './CommitPanelOverview.react';

import './CommitPanel.less';

interface CommitPanelProps extends React.Props<JSX.Element> {
  diffProvider: {getDiff: (hash: string) => Promise<string>};
  gitStatusProvider: {getGitStatus: () => Promise<string[][]>};
  onCommit: (message: string) => any;
  onDiscard: () => any;
}

interface CommitPanelState {
  detailsLevel?: string;
  diff?: string;
  gitStatus?: string[][];
  error?: string;
  loading?: boolean;
}

export default class CommitPanel extends React.Component<CommitPanelProps, CommitPanelState> {

  constructor() {
    super();
    this.state = {detailsLevel: 'none', loading: false};
  }

  render() {
    const className = 'CommitPanel-notice' + (this.state.detailsLevel !== 'none' ? ' CommitPanel-notice--expanded' : '');

    return (
      <div className='CommitPanel'>
        <div className={className}>
          <CommitPanelNotice
            onDetailsLevelChanged={detailsLevel => this.changeDetailsLevel(detailsLevel)}
            detailsLevel={this.state.detailsLevel}
          />
          {this.state.detailsLevel !== 'none'
            ? <CommitPanelCommit
                onCommit={this.props.onCommit}
                onDiscard={this.props.onDiscard}
              />
            : null
          }
        </div>
        {this.renderDetails()}
      </div>
    );
  }

  private renderError() {
    return (
      <div className='CommitPanel-error'>
        <p>{this.state.error}</p>
      </div>
    );
  }

  private renderDetails() {
    if (!this.state.error && this.state.detailsLevel === 'none') {
      return null;
    }
    const className = 'CommitPanel-details' + (this.state.loading ? ' loading' : '');
    const content = this.state.detailsLevel === 'overview'
      ? <CommitPanelOverview gitStatus={this.state.gitStatus} />
      : <CommitPanelDetails diff={this.state.diff} />;

    return (
      <div className={className}>
        {this.renderToggle()}
        {this.state.loading
          ? <div className='CommitPanel-details-loader'></div>
          : null
        }
        {this.state.error
          ? this.renderError()
          : content
        }
      </div>
    );
  }

  private renderToggle() {
    if (this.state.detailsLevel === 'none') {
      return null;
    }

    return (
      <div className='CommitPanel-details-buttons'>
        <button
          className='button'
          disabled={this.state.detailsLevel === 'overview'}
          onClick={() => this.changeDetailsLevel('overview')}
        >Overview</button>
        <button
          className='button'
          disabled={this.state.detailsLevel === 'full-diff'}
          onClick={() => this.changeDetailsLevel('full-diff')}
        >Full diff</button>
      </div>
    );
  }

  private changeDetailsLevel(detailsLevel: string) {
    if (detailsLevel === 'overview' && !this.state.gitStatus) {
      this.setState({loading: true});
      this.props.gitStatusProvider.getGitStatus()
        .then(gitStatus => this.setState({
            detailsLevel: detailsLevel,
            gitStatus: gitStatus,
            error: null,
            loading: false
          })
        ).catch(err => {
          this.setState({detailsLevel: detailsLevel, error: err.message, loading: false});
        });
    } else if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.setState({loading: true});
      this.props.diffProvider.getDiff('')
        .then(diff => this.setState({
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
