import * as React from 'react';
import * as classNames from 'classnames';

import { DetailsLevel } from '../enums/enums';

import CommitPanelCommit from './CommitPanelCommit.react';
import Notice from './Notice';
import CommitPanelDetails from './CommitPanelDetails.react';
import CommitPanelOverview from './CommitPanelOverview.react';

import './CommitPanel.less';

interface CommitPanelProps {
  diffProvider: { getDiff(hash: string): Promise<string> };
  gitStatusProvider: { getGitStatus(): Promise<string[][]> };
  onCommit(message: string): void;
  onDiscard(): void;
}

interface CommitPanelState {
  detailsLevel?: DetailsLevel;
  diff?: string;
  gitStatus?: string[][];
  error?: string;
  isLoading?: boolean;
}

export default class CommitPanel extends React.Component<CommitPanelProps, CommitPanelState> {

  state = {
    detailsLevel: DetailsLevel.None,
    diff: null,
    gitStatus: null,
    error: null,
    isLoading: false,
  };

  onChangeDetailsLevel = (detailsLevel: DetailsLevel) => {
    if (detailsLevel === DetailsLevel.Overview && !this.state.gitStatus) {
      this.setState({
        isLoading: true,
      });

      this.props.gitStatusProvider.getGitStatus()
        .then(gitStatus => this.setState({
            detailsLevel: detailsLevel,
            gitStatus: gitStatus,
            error: null,
            isLoading: false,
          })
        ).catch(err => this.setState({
            detailsLevel: detailsLevel,
            error: err.message,
            isLoading: false,
          })
        );
    } else if (detailsLevel === DetailsLevel.FullDiff && !this.state.diff) {
      this.setState({
        isLoading: true,
      });

      this.props.diffProvider.getDiff('')
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

  private renderError() {
    return (
      <div className='CommitPanel-error'>
        <p>{this.state.error}</p>
      </div>
    );
  }

  private renderDetails() {
    const { detailsLevel, diff, gitStatus, error, isLoading } = this.state;

    if (!error && detailsLevel === DetailsLevel.None) {
      return null;
    }

    const detailsClassName = classNames({
      'CommitPanel-details': true,
      'loading': isLoading,
    });
    const content = detailsLevel === DetailsLevel.Overview
      ? <CommitPanelOverview gitStatus={gitStatus} />
      : <CommitPanelDetails diff={diff} />;

    return (
      <div className={detailsClassName}>
        {this.renderToggle()}
        {isLoading
          ? <div className='CommitPanel-details-loader'></div>
          : null
        }
        {error
          ? this.renderError()
          : content
        }
      </div>
    );
  }

  private renderToggle() {
    const { detailsLevel } = this.state;

    if (detailsLevel === DetailsLevel.None) {
      return null;
    }

    return (
      <div className='CommitPanel-details-buttons'>
        <button
          className='button'
          disabled={detailsLevel === DetailsLevel.Overview}
          onClick={() => this.onChangeDetailsLevel(DetailsLevel.Overview)}
        >Overview</button>
        <button
          className='button'
          disabled={detailsLevel === DetailsLevel.FullDiff}
          onClick={() => this.onChangeDetailsLevel(DetailsLevel.FullDiff)}
        >Full diff</button>
      </div>
    );
  }

  render() {
    const { detailsLevel } = this.state;

    const noticeClassName = classNames({
      'CommitPanel-notice': true,
      'CommitPanel-notice--expanded': detailsLevel !== DetailsLevel.None,
    });

    return (
      <div className='CommitPanel'>
        <div className={noticeClassName}>
          <Notice
            onDetailsLevelChange={this.onChangeDetailsLevel}
            detailsLevel={detailsLevel}
          />
          {detailsLevel !== DetailsLevel.None
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
