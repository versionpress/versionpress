import * as React from 'react';

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

    this.props.onQueryChange((e.target as any).value);
  };

  render() {
    const { query } = this.props;

    return (
      <div className='Filter'>
        <form
          action=''
          method='post'
          onSubmit={this.onSubmit}
        >
          <p className='search-box'>
            <input
              type='search'
              className='Filter-query'
              value={query}
              onChange={this.onInputChange}
            />
            <input
              type='submit'
              className='button'
              value='Search'
            />
          </p>
        </form>
      </div>
    );
  }

}
