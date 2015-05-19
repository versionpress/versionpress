/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');
import {RouteHandler} from 'react-router';

class App extends React.Component<any, any> {

  render() {
    return React.DOM.div(null,
      React.createElement(RouteHandler, {})
    );
  }
}

export = App;
