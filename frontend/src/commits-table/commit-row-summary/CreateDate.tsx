import * as React from 'react';
import * as moment from 'moment';

interface CreateDateProps {
  date: string;
}

const CreateDate: React.StatelessComponent<CreateDateProps> = ({ date }) => (
  <td
    className='column-date'
    title={moment(date).format('LLL')}
  >
    {moment(date).fromNow()}
  </td>
);

export default CreateDate;
