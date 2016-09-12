import * as React from 'react';

interface QueryInputProps {
  query: string;
  onChange(e: React.FormEvent): void;
}

const QueryInput: React.StatelessComponent<QueryInputProps> = ({ query, onChange }) => (
  <input
    type='search'
    className='Filter-query'
    value={query}
    onChange={onChange}
  />
);

export default QueryInput;
