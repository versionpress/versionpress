/// <reference path='./VersionPressConfig.d.ts' />

interface AppWindow extends Window {
  VP_API_Config: VersionPressConfig;
}

declare const window: AppWindow;

export default window;
