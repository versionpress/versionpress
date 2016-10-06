import { action, computed, observable } from 'mobx';

import DetailsLevel from '../enums/DetailsLevel';

class CommitRow {

  @observable commit: Commit = null;
  @observable isSelected: boolean = false;
  @observable detailsLevel: DetailsLevel = DetailsLevel.None;
  @observable diff: string = null;
  @observable error: string = null;
  @observable isLoading: boolean = false;
  @observable visualization: Visualization = null;

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

  @action setVisualization = (visualization: Visualization) => {
    this.visualization = visualization;
  }

}

export default CommitRow;
