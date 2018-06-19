import * as React from 'react';
import { inject, observer } from 'mobx-react';

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

@inject('searchStore')
@observer
export default class Filter extends React.Component<FilterProps, {}> {

  render() {
    const { query, searchStore, onQueryChange, onFilter } = this.props;
    const { config } = searchStore!;

    return (
      <div className='Filter'>
        <form action='' method='post' onSubmit={e => { e.preventDefault(); onFilter(); }}>
          <div className='search-box'>
            {config &&
              <Search config={config} onChange={onQueryChange} />
            }
            {!config &&
              <QueryInput query={query} onChange={onQueryChange} />
            }
            <Submit />
          </div>
        </form>
      </div>
    );
  }

}
