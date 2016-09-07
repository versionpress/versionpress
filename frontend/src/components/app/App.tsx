import * as React from 'react';
import {RouteHandler} from 'react-router';

import './App.less';

export default class App extends React.Component<{}, {}> {

  render() {
    return <RouteHandler />;
  }

}
