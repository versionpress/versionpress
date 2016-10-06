/// <reference path='../components/common/Commits.d.ts' />

import { action, computed, observable } from 'mobx';

import { checkUpdate } from '../actions';
import { parsePageNumber } from '../actions/utils';

class AppStore {

  @observable page: number = 0;
  @observable selectedCommits: Commit[] = [];
  @observable lastSelectedCommit: Commit = null;
  @observable displayWelcomePanel: boolean = false;
  @observable displayUpdateNotice: boolean = false;
  @observable isDirtyWorkingDirectory: boolean = false;

  refreshInterval;

  constructor() {
    this.refreshInterval = setInterval(checkUpdate, 10 * 1000);
  }

  @computed get enableActions() {
    return !this.isDirtyWorkingDirectory;
  }

  @action setPage = (page: number | string) => {
    this.page = parsePageNumber(page);
  }

  @action setDisplayUpdateNotice = (displayUpdateNotice: boolean) => {
    this.displayUpdateNotice = displayUpdateNotice;
  }

  @action setDisplayWelcomePanel = (displayWelcomePanel: boolean) => {
    this.displayWelcomePanel = displayWelcomePanel;
  }

  @action setDirtyWorkingDirectory = (isDirtyWorkingDirectory: boolean) => {
    this.isDirtyWorkingDirectory = isDirtyWorkingDirectory;
  }

  @action setLastSelectedCommit = (lastSelectedCommit: Commit) => {
    this.lastSelectedCommit = lastSelectedCommit;
  }

  @action setSelectedCommits = (selectedCommits: Commit[]) => {
    this.selectedCommits = selectedCommits;
  }

}

const appStore = new AppStore();

export { AppStore };
export default appStore;
