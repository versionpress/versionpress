import * as React from 'react';
import { observer } from 'mobx-react';
import * as moment from 'moment';

interface DateProps {
  date: string;
}

const Date: React.StatelessComponent<DateProps> = ({ date }) => (
  <td className='column-date' title={moment(date).format('LLL')}>
    {moment(date).fromNow()}
  </td>
);

export default observer(Date);
