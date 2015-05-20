/// <reference path='../typings/typings.d.ts' />

import React = require('react');
import Router = require('react-router');
import routes = require('./routes');

const app = document.getElementById('app');

Router.run(routes.appRoute, Router.HashLocation, (Handler) => {
  React.render(
    React.createElement(Handler, {}),
    app
  );
});

