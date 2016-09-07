import * as React from 'react';
import {Route, DefaultRoute, NotFoundRoute } from 'react-router';

import App from '../components/app/App';
import HomePage from '../components/home/HomePage';
import NotFoundPage from '../components/not-found/NotFoundPage';
import config from '../config/config';

export const routes = config.routes;

export const appRoute = (
  <Route path='/' handler={App}>
    <Route name={routes.page} path='page/:page' handler={HomePage} />
    <DefaultRoute name={routes.home} handler={HomePage} />
    <NotFoundRoute name={routes.notFound} handler={NotFoundPage} />
  </Route>
);
