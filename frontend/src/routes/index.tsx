import * as React from 'react';
import { Router, Route, IndexRoute, useRouterHistory, browserHistory } from 'react-router';
import { createHashHistory } from 'history';

import App from '../components/app/App';
import HomePage from '../components/home/HomePage';
import NotFoundPage from '../components/not-found/NotFoundPage';

// Remove the '_k' parameter from url
const appHistory = useRouterHistory(createHashHistory)({ queryKey: false });

const routes = (
  <Router history={appHistory}>
    <Route path='/' component={App}>
      <Route path='page/:page' component={HomePage} />
      <IndexRoute component={HomePage} />
      <Route path='*' component={NotFoundPage} />
    </Route>
  </Router>
);

export {
  appHistory,
  routes,
};
