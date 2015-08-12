/// <reference path='../../typings/tsd.d.ts' />

import config = require('../config');
import request = require('superagent');

const WpApi = {

  getApiLink: (endpoint: string) => {
    if (config.apiPrettyPermalinks) {
      return config.apiBaseUrl + '/' + config.apiUrlPrefix + '/versionpress/' + endpoint;
    } else {
      return config.apiBaseUrl + '/?' + config.apiQueryParam + '=/versionpress/' + endpoint;
    }
  },

  get: (endpoint: string) => {
    const req = request
      .get(WpApi.getApiLink(endpoint))
      .accept('application/json');

    return config.apiNonce
      ? req.set('X-WP-Nonce', config.apiNonce)
      : req;
  },

  post: (endpoint: string) => {
    const req = request
      .post(WpApi.getApiLink(endpoint))
      .accept('application/json');

    return config.apiNonce
      ? req.set('X-WP-Nonce', config.apiNonce)
      : req;
  }

};

export = WpApi;
