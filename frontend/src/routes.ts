/// <reference path='../typings/typings.d.ts' />

import App = require('./app/App.react');
import HomePage = require('./pages/HomePage.react');
import NotFoundPage = require('./pages/NotFoundPage.react');
import React = require('react');
import ReactRouter = require('react-router');
import config = require('./config');

export const routes = config.routes;

export const appRoute = React.createElement(ReactRouter.Route, {path: '/', handler: App},
  React.createElement(ReactRouter.Route, {name: routes.page, path: 'page/:page', handler: HomePage}),
  React.createElement(ReactRouter.DefaultRoute, {name: routes.home, handler: HomePage}),
  React.createElement(ReactRouter.NotFoundRoute, {name: routes.notFound, handler: NotFoundPage})
);
