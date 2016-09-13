/// <reference path='../common/Commits.d.ts' />
/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
import * as ReactRouter from 'react-router';
import * as moment from 'moment';
import * as classNames from 'classnames';
import { observer } from 'mobx-react';

import BulkActionPanel from '../bulk-action-panel/BulkActionPanel';
import CommitPanel from '../commit-panel/CommitPanel';
import CommitsTable from '../commits-table/CommitsTable';
import Filter from '../filter/Filter';
import FlashMessage from '../common/flash-message/FlashMessage';
import ProgressBar from '../common/progress-bar/ProgressBar';
import ServicePanel from '../service-panel/ServicePanel';
import UpdateNotice from './update-notice/UpdateNotice';
import VpTitle from './vp-title/VpTitle';
import WelcomePanel from '../welcome-panel/WelcomePanel';
import { revertDialog } from '../portal/portal';
import { getDiff, getGitStatus } from './utils';

import appStore from '../../stores/appStore';

import './HomePage.less';

interface HomePageProps {
  params: {
    page?: string,
  };
}

interface HomePageContext {
  router: ReactRouter.Context;
}

@observer
export default class HomePage extends React.Component<HomePageProps, {}> {

  static contextTypes: React.ValidationMap<any> = {
    router: React.PropTypes.func.isRequired,
  };

  context: HomePageContext;

  componentDidMount() {
    appStore.updatePage(this.props.params.page);
    appStore.setRouter(this.context.router);
    appStore.fetchWelcomePanel();
    appStore.fetchCommits();
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    appStore.updatePage(nextProps.params.page);
    appStore.fetchCommits();
  }

  fetchCommits = () => {
    appStore.fetchCommits()
  };

  undoCommits = (commits: string[]) => {
    appStore.undoCommits(commits);
  };

  rollbackToCommit = (hash: string) => {
    appStore.rollbackToCommit(hash);
  };

  onServicePanelClick = () => {
    appStore.changeDisplayServicePanel();
  };

  onCommitsSelect = (commitsToSelect: Commit[], isChecked: boolean, isShiftKey: boolean) => {
    appStore.onCommitsSelect(commitsToSelect, isChecked, isShiftKey);
  };

  onBulkAction = (action: string) => {
    if (action === 'undo') {
      const { selectedCommits } = appStore;
      const count = selectedCommits.length;

      const title = (
        <span>Undo <em>{count} {count === 1 ? 'change' : 'changes'}</em>?</span>
      );
      const hashes = selectedCommits.map((commit: Commit) => commit.hash);

      revertDialog(title, () => this.undoCommits(hashes));
    }
  };

  onClearSelection = () => {
    appStore.clearSelection();
  };

  onCommit = (message: string) => {
    appStore.onCommit(message);
  };

  onDiscard = () => {
    appStore.onDiscard();
  };

  onFilterQueryChange = (query: string) => {
    appStore.onFilterQueryChange(query);
  };

  onFilter = () => {
    appStore.onFilter();
  };

  onUndo = (hash: string, message: string) => {
    const title = (
      <span>Undo <em>{message}</em>?</span>
    );

    revertDialog(title, () => this.undoCommits([hash]));
  };

  onRollback = (hash: string, date: string) => {
    const title = (
      <span>Roll back to <em>{moment(date).format('LLL')}</em>?</span>
    );

    revertDialog(title, () => this.rollbackToCommit(hash));
  };

  onWelcomePanelHide = (e: React.MouseEvent) => {
    e.preventDefault();

    appStore.onWelcomePanelHide();
  };

  onUpdateNoticeClick = (e: React.MouseEvent) => {
    e.preventDefault();

    appStore.fetchCommits();
  };

  render() {
    const {
      pages,
      query,
      commits,
      selectedCommits,
      message,
      isLoading,
      displayServicePanel,
      displayWelcomePanel,
      displayUpdateNotice,
      isDirtyWorkingDirectory,
      progress,
    } = appStore;

    const homePageClassName = classNames({
      'loading': isLoading,
    });

    return (
      <div className={homePageClassName}>
        <ProgressBar progress={progress} />
        <ServicePanel
          isVisible={displayServicePanel}
          onButtonClick={this.onServicePanelClick}
        >
          <VpTitle />
          {message &&
            <FlashMessage message={message} />
          }
        </ServicePanel>
        {isDirtyWorkingDirectory &&
          <CommitPanel
            diffProvider={{ getDiff: getDiff }}
            gitStatusProvider={{ getGitStatus: getGitStatus }}
            onCommit={this.onCommit}
            onDiscard={this.onDiscard}
          />
        }
        {displayWelcomePanel &&
          <WelcomePanel onHide={this.onWelcomePanelHide} />
        }
        {displayUpdateNotice &&
          <UpdateNotice onClick={this.onUpdateNoticeClick} />
        }
        <div className='tablenav top'>
          <Filter
            query={query}
            onQueryChange={this.onFilterQueryChange}
            onFilter={this.onFilter}
          />
          <BulkActionPanel
            enableActions={!isDirtyWorkingDirectory}
            onBulkAction={this.onBulkAction}
            onClearSelection={this.onClearSelection}
            selectedCommits={selectedCommits}
          />
        </div>
        <CommitsTable
          pages={pages}
          commits={commits}
          selectedCommits={selectedCommits}
          enableActions={!isDirtyWorkingDirectory}
          diffProvider={{ getDiff: getDiff }}
          onUndo={this.onUndo}
          onRollback={this.onRollback}
          onCommitsSelect={this.onCommitsSelect}
        />
      </div>
    );
  }

}
