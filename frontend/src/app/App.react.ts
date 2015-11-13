/// <reference path='../../typings/typings.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');

require('./App.less');

class App extends React.Component<{}, {}> {

  static contextTypes: React.ValidationMap<any> = {
    router: React.PropTypes.func.isRequired
  };

  render() {
    return React.createElement(ReactRouter.RouteHandler, {
      router: (<any> this.context).router
    });
  }

}

export = App;
