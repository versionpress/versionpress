/// <reference path='../../typings/typings.d.ts' />

import React = require('react');
import ReactRouter = require('react-router');

class App extends React.Component<any, any> {

  render() {
    return React.DOM.div(null,
      React.createElement(ReactRouter.RouteHandler, {})
    );
  }
}

export = App;
