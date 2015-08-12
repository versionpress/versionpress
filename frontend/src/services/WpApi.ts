import config = require('../config');

const WpApi = {

  getApiLink: (endpoint: string) => {
    if (config.apiPrettyPermalinks) {
      return config.apiBaseUrl + '/' + config.apiUrlPrefix + '/versionpress/' + endpoint;
    } else {
      return config.apiBaseUrl + '/?' + config.apiQueryParam + '=/versionpress/' + endpoint;
    }
  }

};

export = WpApi;
