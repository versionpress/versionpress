/// <reference path='../../../typings/tsd.d.ts' />

import ReactRouter = require('react-router');

const reactRouterStub: ReactRouter.Context = <ReactRouter.Context> {
  makePath: () => null,
  makeHref: () => null,
  transitionTo: () => null,
  replaceWith: () => null,
  goBack: () => null,

  getCurrentPath: () => null,
  getCurrentRoutes: () => null,
  getCurrentPathname: () => null,
  getCurrentParams: () => null,
  getCurrentQuery: () => null,
  isActive: () => null
};

export = reactRouterStub;
