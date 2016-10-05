import * as React from 'react';
import * as DOM from 'react-dom';
import { Router } from 'react-router';

import 'core-js';

import { appHistory, routes } from './routes';
const app = document.getElementById('vp');

DOM.render(
    <Router history={appHistory}>
      {routes}
    </Router>
, app);
