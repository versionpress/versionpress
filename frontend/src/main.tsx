import 'core-js';

import * as React from 'react';
import { render } from 'react-dom';
import { useStrict } from 'mobx';
import { Provider } from 'mobx-react';

import { routes } from './routes';
import * as stores from './stores';

import App from './components/app/App';

// Disables changing state outside of an action
useStrict(true);

const root = document.getElementById('vp');

render(
  <Provider {...stores}>
      {routes}
  </Provider>,
  root,
);
