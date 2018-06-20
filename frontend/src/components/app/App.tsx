import * as React from 'react';
import { inject, observer } from 'mobx-react';
import { Route, RouteComponentProps, Switch } from 'react-router-dom';

import HomePage from '../home/HomePage';
import NotFoundPage from '../not-found/NotFoundPage';

import { AppStore } from '../../stores/appStore';

import './App.less';

interface AppProps extends RouteComponentProps<void> {
  appStore?: AppStore;
  children: React.ReactNode;
}

@inject('appStore')
@observer
export default class App extends React.Component<AppProps, {}> {

  componentDidMount() {
    const { appStore, history } = this.props;

    appStore!.setAppHistory(history);
  }

  render() {
    return (
      <div>
        <Switch>
          <Route path='/' exact component={HomePage} />
          <Route path='/page/:page' component={HomePage} />
          <Route path='*' component={NotFoundPage} />
        </Switch>
      </div>
    );
  }

}
