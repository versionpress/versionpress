import { action, observable } from 'mobx';

import commitsTableStore from './commitsTableStore';

class ServicePanelStore {
  @observable message: InfoMessage = null;
  @observable isVisible: boolean = false;
  @observable isVisualizationVisible: boolean = false;

  get commits() {
    return commitsTableStore.commits;
  }

  @action
  changeMessage = (message: InfoMessage) => {
    this.message = message;
  };

  @action
  changeVisibility = (isVisible?: boolean) => {
    this.isVisible = typeof isVisible === 'boolean' ? isVisible : !this.isVisible;
    this.changeVisualizationVisibility();
  };

  @action
  changeVisualizationVisibility = (isVisible?: boolean) => {
    this.isVisualizationVisible = typeof isVisible === 'boolean'
      ? isVisible
      : !this.isVisualizationVisible;
  };
}

const servicePanelStore = new ServicePanelStore();

export { ServicePanelStore };
export default servicePanelStore;
