/// <reference path='../typings/tsd.d.ts' />
/// <reference path='./VersionPressConfig.d.ts' />

import localConfig = require('./config.local');

require('core-js/es6/object');

const defaultConfig: VersionPressConfig = {

  api: {
    root: '',
    urlPrefix: 'vp-json',
    queryParam: 'vp_rest_route',
    prettyPermalinks: false
  },

  routes: {
    page: 'page',
    home: 'home',
    notFound: 'not-found'
  }

};

const VpApiConfig = {
  api: window['VP_API_Config'] || {}
};

const config: VersionPressConfig = Object.assign(defaultConfig, localConfig, VpApiConfig);
export = config;
