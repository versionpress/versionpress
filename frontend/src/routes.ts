/// <reference path='../typings/typings.d.ts' />

import App = require('./app/App.react');
import HomePage = require('./pages/HomePage.react');
import NotFoundPage = require('./pages/NotFoundPage.react');
import React = require('react');
import Router = require('react-router');

export const defaultRoute = React.createElement(Router.DefaultRoute, {name: 'home', handler: HomePage});
export const notFoundRoute = React.createElement(Router.NotFoundRoute, {name: 'not-found', handler: NotFoundPage});

export const appRoute = React.createElement(Router.Route, {path: '/', handler: App},
  defaultRoute,
  notFoundRoute
);
