import { action, observable, runInAction } from 'mobx';

import DetailsLevel from '../enums/DetailsLevel';
import { getDiff, getGitStatus } from './utils';

class CommitPanelStore {
  @observable detailsLevel: DetailsLevel = DetailsLevel.None;
  @observable diff: string = null;
  @observable gitStatus: VpApi.GetGitStatusResponse = null;
  @observable error: string = null;
  @observable isLoading: boolean = false;

  @action
  changeDetailsLevel = (detailsLevel: DetailsLevel) => {
    if (detailsLevel === DetailsLevel.Overview && !this.gitStatus) {
      this.isLoading = true;
      getGitStatus()
        .then(this.handleSuccess(detailsLevel))
        .catch(this.handleError(detailsLevel));
      return;
    }

    if (detailsLevel === DetailsLevel.FullDiff && !this.diff) {
      this.isLoading = true;
      getDiff('')
        .then(this.handleSuccess(detailsLevel))
        .catch(this.handleError(detailsLevel));
      return;
    }

    this.detailsLevel = detailsLevel;
    this.error = null;
    this.isLoading = false;
  };

  @action
  private handleSuccess = (detailsLevel: DetailsLevel) => {
    if (detailsLevel === DetailsLevel.Overview) {
      return gitStatus => runInAction(() => {
        this.detailsLevel = detailsLevel;
        this.gitStatus = gitStatus;
        this.error = null;
        this.isLoading = false;
      });
    } else if (detailsLevel === DetailsLevel.FullDiff) {
      return diff => runInAction(() => {
        this.detailsLevel = detailsLevel;
        this.diff = diff;
        this.error = null;
        this.isLoading = false;
      });
    }
  };

  @action
  private handleError = (detailsLevel: DetailsLevel) => {
    return err => runInAction(() => {
      this.detailsLevel = detailsLevel;
      this.error = err.message;
      this.isLoading = false;
    });
  };
}

const commitPanelStore = new CommitPanelStore();

export { CommitPanelStore };
export default commitPanelStore;
