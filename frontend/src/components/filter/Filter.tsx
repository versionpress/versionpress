import * as React from 'react';
import { observer } from 'mobx-react';

import QueryInput from './QueryInput';
import Submit from './Submit';
import Search from '../search/Search';

import { SearchStore } from '../../stores/searchStore';

import './Filter.less';

interface FilterProps {
  query: string;
  searchStore?: SearchStore;
  onQueryChange(query: string): void;
  onFilter(): void;
}

@observer(['searchStore'])
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
    const { query, searchStore } = this.props;
    const { config } = searchStore;

    return (
      <div className='Filter'>
        <form action='' method='post' onSubmit={this.onSubmit}>
          <div className='search-box'>
            {config &&
              <Search config={config} />
            }
            {!config &&
              <QueryInput query={query} onChange={this.onInputChange} />
            }
            <Submit />
          </div>
        </form>
      </div>
    );
  }

}
