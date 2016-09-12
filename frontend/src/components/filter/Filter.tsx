import * as React from 'react';

import QueryInput from './QueryInput';
import Submit from './Submit';

import './Filter.less';

interface FilterProps {
  query: string;
  onQueryChange(query: string): void;
  onFilter(): void;
}

export default class Filter extends React.Component<FilterProps, {}> {

  onSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    this.props.onFilter();
  };

  onInputChange = (e: React.FormEvent) => {
    e.preventDefault();

    this.props.onQueryChange((e.target as HTMLTextAreaElement).value);
  };

  render() {
    const { query } = this.props;

    return (
      <div className='Filter'>
        <form action='' method='post' onSubmit={this.onSubmit}>
          <p className='search-box'>
            <QueryInput query={query} onChange={this.onInputChange} />
            <Submit />
          </p>
        </form>
      </div>
    );
  }

}
