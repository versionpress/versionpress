import * as React from 'react';
import { Route, IndexRoute, useRouterHistory } from 'react-router';
import { createHashHistory } from 'history';

import App from '../components/app/App';
import HomePage from '../components/home/HomePage';
import NotFoundPage from '../components/not-found/NotFoundPage';

const routes = (
  <Route path='/' component={App}>
    <Route path='page/:page' component={HomePage} />
    <IndexRoute component={HomePage} />
    <Route path='*' component={NotFoundPage} />
  </Route>
);

// Remove the '_k' parameter from url
const appHistory = useRouterHistory(createHashHistory)({ queryKey: false });

export {
  appHistory,
  routes,
};
