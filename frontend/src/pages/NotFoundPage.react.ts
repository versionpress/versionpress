/// <reference path='../../typings/typings.d.ts' />

import React = require('react');

const DOM = React.DOM;

class NotFoundPage extends React.Component<{}, {}> {

  render() {
    return DOM.div(null,
      DOM.h1(null, 'Not found.')
    );
  }

}

export = NotFoundPage;
