/// <reference path='../typings/tsd.d.ts' />

import React = require('react');
import Router = require('react-router');
import routes = require('./routes');

const app = document.getElementById('vp');

Router.run(routes.appRoute, Router.HashLocation, (handler) => {
  React.render(
    React.createElement(handler, {}),
    app
  );
});

