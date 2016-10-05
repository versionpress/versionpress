/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import { observer } from 'mobx-react';

import Meta from './Meta';
import { LineProps } from './Line';

const UserMeta: React.StatelessComponent<LineProps> = (props) => (
  <Meta
    parentEntity='user'
    groupByTag='VP-User-Login'
    {...props}
  />
);

export default observer(UserMeta);
