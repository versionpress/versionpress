import localConfig = require('./config.local');

var config = {

  apiBaseUrl: window['apiBaseUrl'] || localConfig['apiBaseUrl'] || '',

  routes: {
    page: 'page',
    home: 'home',
    notFound: 'not-found'
  }

};

export = config;
