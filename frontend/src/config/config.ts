/// <reference path='./VersionPressConfig.d.ts' />

import * as _ from 'lodash';
import localConfig from './config.local';

const defaultConfig: VersionPressConfig = {
  api: {
    root: '',
    adminUrl: '',
    urlPrefix: 'wp-json',
    queryParam: 'rest_route',
    permalinkStructure: false,
    nonce: null,
  },
  routes: {
    page: 'page',
    home: '/',
  },
};

const vpApiConfig = {
  api: window['VP_API_Config'] || {},
};

let config = <VersionPressConfig> _.merge(defaultConfig, localConfig, vpApiConfig);

export default config;
