import { action, observable } from 'mobx';

class ServicePanelStore {
  @observable message: InfoMessage = null;
  @observable isVisible: boolean = false;

  @action
  changeMessage = (message: InfoMessage) => {
    this.message = message;
  };

  @action
  changeVisibility = (isVisible?: boolean) => {
    this.isVisible = typeof isVisible === 'boolean' ? isVisible : !this.isVisible;
  }
}

const servicePanelStore = new ServicePanelStore();

export { ServicePanelStore };
export default servicePanelStore;
