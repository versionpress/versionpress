import 'core-js';

import * as React from 'react';
import { render } from 'react-dom';
import { HashRouter, Route } from 'react-router-dom';
import { Provider } from 'mobx-react';
import { hot } from 'react-hot-loader';

import * as stores from './stores';

import App from './components/app/App';

declare const module: { hot: any };

const HotComponent = (Component: any) => hot(module)(Component);

const root = document.getElementById('vp');

render(
  <Provider {...stores}>
    <HashRouter>
      <Route component={HotComponent(App)} />
    </HashRouter>
  </Provider>,
  root,
);
