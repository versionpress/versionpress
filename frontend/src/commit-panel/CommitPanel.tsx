import * as React from 'react';
import * as classNames from 'classnames';

import { DetailsLevel } from '../enums/enums';

import Commit from './commit/Commit';
import Notice from './Notice';
import Details from './details/Details';

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
          {detailsLevel !== DetailsLevel.None
            ? <Commit
                onCommit={onCommit}
                onDiscard={onDiscard}
              />
            : null
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
