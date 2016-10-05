import * as React from 'react';
import * as DOM from 'react-dom';
import { Provider } from 'mobx-react';
import { Router } from 'react-router';

// Polyfills for ES5, ES6, ES7
import 'core-js';

import { appHistory, routes } from './routes';
import * as stores from './stores';
const app = document.getElementById('vp');

DOM.render(
  <Provider {...stores}>
    <Router history={appHistory}>
      {routes}
    </Router>
  </Provider>
, app);
