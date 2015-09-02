/// <reference path='../typings/tsd.d.ts' />
/// <reference path='./VersionPressConfig.d.ts' />

import _ = require('lodash');
import localConfig = require('./config.local');

const defaultConfig: VersionPressConfig = {

  api: {
    root: '',
    urlPrefix: 'vp-json',
    queryParam: 'vp_rest_route',
    prettyPermalinks: false,
    nonce: null
  },

  routes: {
    page: 'page',
    home: 'home',
    notFound: 'not-found'
  }

};

const vpApiConfig = {
  api: window['VP_API_Config'] || {}
};

var config = <VersionPressConfig> _.merge(defaultConfig, localConfig, vpApiConfig);

export = config;
