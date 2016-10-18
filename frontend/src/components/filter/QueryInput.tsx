import * as React from 'react';
import { observer } from 'mobx-react';

interface QueryInputProps {
  query: string;
  onChange(query: string): void;
}

const QueryInput: React.StatelessComponent<QueryInputProps> = ({ query, onChange }) => (
  <input
    type='search'
    className='Filter-query'
    value={query}
    onChange={e => onChange(e.currentTarget.value)}
  />
);

export default observer(QueryInput);
