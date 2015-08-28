/// <reference path='../../typings/tsd.d.ts' />

import config = require('../config');
import request = require('superagent');

function getApiLink(endpoint: string) {
  if (config.api.prettyPermalinks) {
    return config.api.root + '/' + config.api.urlPrefix + '/versionpress/' + endpoint;
  } else {
    return config.api.root + '/?' + config.api.queryParam + '=/versionpress/' + endpoint;
  }
}

export function get(endpoint: string) {
  const req = request
    .get(getApiLink(endpoint))
    .accept('application/json');

  return config.api.nonce
    ? req.set('X-WP-Nonce', config.api.nonce)
    : req;
}

export function post(endpoint: string) {
  const req = request
    .post(getApiLink(endpoint))
    .accept('application/json');

  return config.api.nonce
    ? req.set('X-WP-Nonce', config.api.nonce)
    : req;
}
