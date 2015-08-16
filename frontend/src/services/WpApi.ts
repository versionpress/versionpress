import config = require('../config');

const WpApi = {

  getApiLink: (endpoint: string) => {
    if (config.api.prettyPermalinks) {
      return config.api.root + '/' + config.api.urlPrefix + '/versionpress/' + endpoint;
    } else {
      return config.api.root + '/?' + config.api.queryParam + '=/versionpress/' + endpoint;
    }
  }

};

export = WpApi;
