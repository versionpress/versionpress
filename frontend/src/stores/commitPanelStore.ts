/// <reference path='../services/VpApi.d.ts' />

import { action, observable } from 'mobx';

import DetailsLevel from '../enums/DetailsLevel';

class CommitPanelStore {

  @observable detailsLevel: DetailsLevel = DetailsLevel.None;
  @observable diff: string;
  @observable gitStatus: VpApi.GetGitStatusResponse;
  @observable error: string;
  @observable isLoading: boolean = false;

  get hash() {
    return '';
  }

  @action setDetailsLevel = (detailsLevel: DetailsLevel) => {
    this.detailsLevel = detailsLevel;
  }

  @action setError = (error: string) => {
    this.error = error;
  }

  @action setLoading = (isLoading: boolean) => {
    this.isLoading = isLoading;
  }

  @action setGitStatus(gitStatus: VpApi.GetGitStatusResponse) {
    this.gitStatus = gitStatus;
  }

  @action setDiff(diff: string) {
    this.diff = diff;
  }

}

const commitPanelStore = new CommitPanelStore();

export { CommitPanelStore };
export default commitPanelStore;
