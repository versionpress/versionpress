import * as React from 'react';

import './App.less';

interface AppProps {
  children: React.ReactNode;
}

export default class App extends React.Component<AppProps, {}> {

  render() {
    const { children } = this.props;

    return (
      <div>
        {children}
      </div>
    );
  }

}
