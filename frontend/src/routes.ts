/// <reference path='../typings/tsd.d.ts' />

import App = require('./app/App.react');
import HomePage = require('./pages/HomePage.react');
import NotFoundPage = require('./pages/NotFoundPage.react');
import React = require('react');
import Router = require('react-router');

export const DefaultRoute = React.createElement(Router.DefaultRoute, {name: "home", handler: HomePage});
export const NotFoundRoute = React.createElement(Router.NotFoundRoute, {name: "not-found", handler: NotFoundPage});

export const AppRoute = React.createElement(Router.Route, {path: "/", handler: App},
  DefaultRoute,
  NotFoundRoute
);
