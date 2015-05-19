/// <reference path='../typings/tsd.d.ts' />

import React = require('react');

const app = document.getElementById('app');

React.render(
  React.DOM.h1(null, "Hello world!"),
  app
);
