/// <reference path='../../typings/tsd.d.ts' />

import React = require('react');

const DOM = React.DOM;

class NotFoundPage extends React.Component<any, any> {

  render() {
    return DOM.h1(null, 'Not found.');
  }

}

export = NotFoundPage;
