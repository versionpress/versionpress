import { action, computed, observable } from 'mobx';

import DetailsLevel from '../enums/DetailsLevel';

class CommitRow {

  @observable commit: Commit;
  @observable isSelected: boolean = false;
  @observable detailsLevel: DetailsLevel = DetailsLevel.None;
  @observable diff: string;
  @observable error: string;
  @observable isLoading: boolean = false;
  @observable visualisation: Visualisation;

  constructor(commit: Commit, isSelected: boolean = false) {
    this.commit = commit;
    this.isSelected = isSelected;
  }

  @computed get hash() {
    return this.commit.hash;
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

  @action setDiff = (diff: string) => {
    this.diff = diff;
  }

  @action setVisualisation = (visualisation: Visualisation) => {
    this.visualisation = visualisation;
  }

}

export default CommitRow;
