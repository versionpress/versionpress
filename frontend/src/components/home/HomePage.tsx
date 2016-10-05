/// <reference path='../common/Commits.d.ts' />
/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import CommitPanel from '../commit-panel/CommitPanel';
import CommitsTable from '../commits-table/CommitsTable';
import Navigation from '../navigation/Navigation';
import ProgressBar from '../progress-bar/ProgressBar';
import ServicePanel from '../service-panel/ServicePanel';
import UpdateNotice from './update-notice/UpdateNotice';
import VpTitle from './vp-title/VpTitle';
import WelcomePanel from '../welcome-panel/WelcomePanel';

import { AppStore } from '../../stores/appStore';

import './HomePage.less';

interface HomePageProps {
  appStore?: AppStore;
  params: {
    page?: string,
  };
}

@observer(['appStore'])
export default class HomePage extends React.Component<HomePageProps, {}> {

  componentDidMount() {
    const { appStore } = this.props;
    appStore.init(this.props.params.page);
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    const { appStore } = this.props;
    const page = nextProps.params.page || 0;

    appStore.fetchCommits(page);
  }

  onWelcomePanelHide = (e: React.MouseEvent) => {
    e.preventDefault();
    const { appStore } = this.props;

    appStore.hideWelcomePanel();
  };

  onUpdateNoticeClick = (e: React.MouseEvent) => {
    e.preventDefault();
    const { appStore } = this.props;

    appStore.fetchCommits();
  };

  render() {
    const {
      displayWelcomePanel,
      displayUpdateNotice,
      isDirtyWorkingDirectory,
      progress,
    } = this.props.appStore;

    return (
      <div>
        <ProgressBar progress={progress} />
        <ServicePanel>
          <VpTitle />
        </ServicePanel>
        {isDirtyWorkingDirectory &&
          <CommitPanel />
        }
        {displayWelcomePanel &&
          <WelcomePanel onHide={this.onWelcomePanelHide} />
        }
        {displayUpdateNotice &&
          <UpdateNotice onClick={this.onUpdateNoticeClick} />
        }
        <Navigation />
        <CommitsTable />
      </div>
    );
  }

}
