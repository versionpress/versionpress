/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');

require('./App.less');

class App extends React.Component<any, any> {

  static contextTypes = {
    router: React.PropTypes.func.isRequired
  };

  render() {
    return React.createElement(ReactRouter.RouteHandler, {
      router: this.context.router
    });
  }

}

export = App;
