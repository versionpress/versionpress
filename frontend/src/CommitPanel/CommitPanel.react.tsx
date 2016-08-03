import * as React from 'react';
import * as classNames from 'classnames';

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
  isLoading?: boolean;
}

export default class CommitPanel extends React.Component<CommitPanelProps, CommitPanelState> {

  state = {
    detailsLevel: 'none',
    diff: null,
    gitStatus: null,
    error: null,
    isLoading: false
  }

  onChangeDetailsLevel = (detailsLevel: string) => {
    if (detailsLevel === 'overview' && !this.state.gitStatus) {
      this.setState({
        isLoading: true
      });

      this.props.gitStatusProvider.getGitStatus()
        .then(gitStatus => this.setState({
            detailsLevel: detailsLevel,
            gitStatus: gitStatus,
            error: null,
            isLoading: false,
          })
        ).catch(err => {
        this.setState({
          detailsLevel: detailsLevel,
          error: err.message,
          isLoading: false
        });
      });
    } else if (detailsLevel === 'full-diff' && !this.state.diff) {
      this.setState({
        isLoading: true
      });

      this.props.diffProvider.getDiff('')
        .then(diff => this.setState({
            detailsLevel: detailsLevel,
            diff: diff,
            error: null,
            isLoading: false,
          })
        ).catch(err => {
        this.setState({
          detailsLevel: detailsLevel,
          error: err.message,
          isLoading: false
        });
      });
    } else {
      this.setState({
        detailsLevel: detailsLevel,
        error: null,
        isLoading: false
      });
    }
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

    const detailsClassName = classNames({
      'CommitPanel-details': true,
      'loading': this.state.isLoading
    });
    const content = this.state.detailsLevel === 'overview'
      ? <CommitPanelOverview gitStatus={this.state.gitStatus} />
      : <CommitPanelDetails diff={this.state.diff} />;

    return (
      <div className={detailsClassName}>
        {this.renderToggle()}
        {this.state.isLoading
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
          onClick={() => this.onChangeDetailsLevel('overview')}
        >Overview</button>
        <button
          className='button'
          disabled={this.state.detailsLevel === 'full-diff'}
          onClick={() => this.onChangeDetailsLevel('full-diff')}
        >Full diff</button>
      </div>
    );
  }

  render() {
    const { detailsLevel } = this.state;

    const noticeClassName = classNames({
      'CommitPanel-notice': true,
      'CommitPanel-notice--expanded': detailsLevel !== 'none',
    });

    return (
      <div className='CommitPanel'>
        <div className={noticeClassName}>
          <CommitPanelNotice
            onDetailsLevelChange={this.onChangeDetailsLevel}
            detailsLevel={detailsLevel}
          />
          {detailsLevel !== 'none'
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

}
