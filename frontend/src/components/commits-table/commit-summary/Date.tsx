import * as React from 'react';
import { observer } from 'mobx-react';
import * as moment from 'moment';

interface DateProps {
  date: string;
}

const Date: React.StatelessComponent<DateProps> = ({ date }) => (
  <div className='column-date' title={moment(date).format('LLL')}>
    {moment(date).fromNow()}
  </div>
);

export default observer(Date);
