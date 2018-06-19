/// <reference path='../interfaces/State.d.ts' />

import { action, observable } from 'mobx';

class ServicePanelStore {

  @observable message: InfoMessage | null = null;
  @observable isVisible: boolean = false;

  @action setMessage = (message: InfoMessage | null) => {
    this.message = message;
  }

  @action toggleVisibility = (isVisible?: boolean) => {
    this.isVisible = typeof isVisible === 'boolean' ? isVisible : !this.isVisible;
  }

}

const servicePanelStore = new ServicePanelStore();

export { ServicePanelStore };
export default servicePanelStore;
