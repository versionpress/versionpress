import * as React from 'react';
import * as classNames from 'classnames';

import Commit from './commit/Commit';
import Details from './details/Details';
import Notice from './Notice';
import DetailsLevel from '../../enums/DetailsLevel';

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
  gitStatus?: VpApi.GetGitStatusResponse;
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

  onDetailsLevelChange = (detailsLevel: DetailsLevel) => {
    const { diffProvider, gitStatusProvider } = this.props;
    const { gitStatus, diff } = this.state;

    if (detailsLevel === DetailsLevel.Overview && !gitStatus) {
      this.setLoading();
      gitStatusProvider.getGitStatus()
        .then(this.handleSuccess(detailsLevel))
        .catch(this.handleError(detailsLevel));
      return;
    }

    if (detailsLevel === DetailsLevel.FullDiff && !diff) {
      this.setLoading();
      diffProvider.getDiff('')
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
    if (detailsLevel === DetailsLevel.Overview) {
      return gitStatus => this.setState({
        detailsLevel: detailsLevel,
        gitStatus: gitStatus,
        error: null,
        isLoading: false,
      });
    } else if (detailsLevel === DetailsLevel.FullDiff) {
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
    const { onCommit, onDiscard } = this.props;
    const { detailsLevel } = this.state;

    const noticeClassName = classNames({
      'CommitPanel-notice': true,
      'CommitPanel-notice--expanded': detailsLevel !== DetailsLevel.None,
    });

    return (
      <div className='CommitPanel'>
        <div className={noticeClassName}>
          <Notice
            onDetailsLevelChange={this.onDetailsLevelChange}
            detailsLevel={detailsLevel}
          />
          {detailsLevel !== DetailsLevel.None &&
            <Commit
              onCommit={onCommit}
              onDiscard={onDiscard}
            />
          }
        </div>
        <Details
          {...this.state}
          onDetailsLevelChange={this.onDetailsLevelChange}
        />
      </div>
    );
  }

}
