/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');

class App extends React.Component<any, any> {

  render() {
    return React.createElement(ReactRouter.RouteHandler, {});
  }

}

export = App;
