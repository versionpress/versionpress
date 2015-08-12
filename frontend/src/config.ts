import localConfig = require('./config.local');

var config = {

  apiBaseUrl: window['apiBaseUrl'] || localConfig['apiBaseUrl'] || '',
  apiUrlPrefix: window['apiUrlPrefix'] || localConfig['apiUrlPrefix'] || 'vp-json',
  apiQueryParam: window['apiQueryParam'] || localConfig['apiQueryParam'] || 'vp_rest_route',
  apiPrettyPermalinks: window['apiPrettyPermalinks'] || localConfig['apiPrettyPermalinks'] || false,
  apiNonce: window['apiNonce'] || null,

  routes: {
    page: 'page',
    home: 'home',
    notFound: 'not-found'
  }

};

export = config;
