/// <reference path='../common/Commits.d.ts' />
/// <reference path='../../interfaces/State.d.ts' />

import * as React from 'react';
import * as ReactRouter from 'react-router';
import { observer } from 'mobx-react';

import CommitPanel from '../commit-panel/CommitPanel';
import CommitsTable from '../commits-table/CommitsTable';
import Navigation from '../navigation/Navigation';
import ProgressBar from '../progress-bar/ProgressBar';
import ServicePanel from '../service-panel/ServicePanel';
import UpdateNotice from './update-notice/UpdateNotice';
import VpTitle from './vp-title/VpTitle';
import WelcomePanel from '../welcome-panel/WelcomePanel';

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
    appStore.init(this.props.params.page, this.context.router);
  }

  componentWillReceiveProps(nextProps: HomePageProps) {
    appStore.fetchCommits(nextProps.params.page);
  }

  onWelcomePanelHide = (e: React.MouseEvent) => {
    e.preventDefault();

    appStore.hideWelcomePanel();
  };

  onUpdateNoticeClick = (e: React.MouseEvent) => {
    e.preventDefault();

    appStore.fetchCommits();
  };

  render() {
    const {
      displayWelcomePanel,
      displayUpdateNotice,
      isDirtyWorkingDirectory,
      progress,
    } = appStore;

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
