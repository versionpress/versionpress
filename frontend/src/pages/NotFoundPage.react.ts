/// <reference path='../../typings/typings.d.ts' />

import React = require('react');

class NotFoundPage extends React.Component<any, any> {

  render() {
    return React.DOM.h1(null, 'Not found.');
  }
}

export = NotFoundPage;
