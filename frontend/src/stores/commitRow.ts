import { action, computed, observable, runInAction } from 'mobx';

import DetailsLevel from '../enums/DetailsLevel';
import { getDiff } from './utils';

import appStore from './appStore';

class CommitRow {
  @observable commit: Commit = null;
  @observable isSelected: boolean = false;
  @observable detailsLevel: DetailsLevel = DetailsLevel.None;
  @observable diff: string = null;
  @observable error: string = null;
  @observable isLoading: boolean = false;

  @computed get enableActions() {
    return !appStore.isDirtyWorkingDirectory;
  }

  constructor(commit: Commit) {
    this.commit = commit;
  }

  private handleSuccess = (detailsLevel: DetailsLevel) => {
    if (detailsLevel === DetailsLevel.FullDiff) {
      return diff => runInAction(() => {
        this.detailsLevel = detailsLevel;
        this.diff = diff;
        this.error = null;
        this.isLoading = false;
      });
    }
  };

  private handleError = (detailsLevel: DetailsLevel) => {
    return err => runInAction(() => {
      this.detailsLevel = detailsLevel;
      this.error = err.message;
      this.isLoading = false;
    });
  };

  @action
  changeDetailsLevel = (detailsLevel: DetailsLevel) => {
    if (detailsLevel === DetailsLevel.FullDiff && !this.diff) {
      this.isLoading = true;

      getDiff(this.commit.hash)
        .then(this.handleSuccess(detailsLevel))
        .catch(this.handleError(detailsLevel));
      return;
    }

    this.detailsLevel = detailsLevel;
    this.error = null;
    this.isLoading = false;
  };

  @action
  undo = () => {
    appStore.undoCommits([this.commit.hash])
  };

  @action
  rollback = () => {
    appStore.rollbackToCommit(this.commit.hash)
  };

  @action
  selectCommit = (isShiftKey: boolean) => {
    this.isSelected = !this.isSelected;

    appStore.selectCommits([this.commit], this.isSelected, isShiftKey);
  }
}

export default CommitRow;
