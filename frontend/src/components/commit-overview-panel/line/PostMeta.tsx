/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';

import Meta from './Meta';
import { LineProps } from './Line';

const PostMeta: React.StatelessComponent<LineProps> = (props) => (
  <Meta
    parentEntity='post'
    groupByTag='VP-Post-Title'
    {...props}
  />
);

export default PostMeta;
