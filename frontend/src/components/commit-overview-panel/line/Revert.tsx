/// <reference path='../../common/Commits.d.ts' />

import * as React from 'react';
import * as moment from 'moment';

import { LineProps } from './Line';

const Revert: React.StatelessComponent<LineProps> = ({ changes }) => {
  const change = changes[0];
  const action = change.action;

  const commitDetails = change.tags['VP-Commit-Details'];
  const message = commitDetails['message'];

  if (action === 'rollback') {
    return (
      <span>
        {`The state is same as it was in "${message}"`}
      </span>
    );
  } else {
    const date = change.tags['VP-Commit-Details']['date'];
    const dateRel = moment(date).fromNow();
    const dateAbs = moment(date).format('LLL');

    return (
      <span>
        {`Reverted change "${message}" was made ${dateRel} (${dateAbs})`}
      </span>
    );
  }
};

export default Revert;
