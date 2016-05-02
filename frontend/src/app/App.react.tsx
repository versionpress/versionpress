/// <reference path='../../typings/browser.d.ts' />

import * as React from 'react';
import {RouteHandler} from 'react-router';

import './App.less';

export default class App extends React.Component<React.Props<JSX.Element>, {}> {

  render() {
    return <RouteHandler />;
  }

}
