/// <reference path='../typings/browser.d.ts' />

import * as React from 'react';
import * as DOM from 'react-dom';
import * as ReactRouter from 'react-router';
import { appRoute } from './routes';

import 'core-js';

const app = document.getElementById('vp');

ReactRouter.run(appRoute, ReactRouter.HashLocation, (Handler) => {
  DOM.render(<Handler />, app);
});
