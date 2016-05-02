/// <reference path='../typings/browser.d.ts' />
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
    home: 'home',
    notFound: 'not-found',
  },
};

const vpApiConfig = {
  api: window['VP_API_Config'] || {},
};

var config = <VersionPressConfig> _.merge(defaultConfig, localConfig, vpApiConfig);

export default config;
