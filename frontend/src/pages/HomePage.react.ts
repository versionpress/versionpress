/// <reference path='../../typings/typings.d.ts' />

import React = require('react');

class HomePage extends React.Component<any, any> {

  render() {
    return React.DOM.h1(null, 'Hello world');
  }
}

export = HomePage;
