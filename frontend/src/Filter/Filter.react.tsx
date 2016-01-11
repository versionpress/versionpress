/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';
import parseQuery from '../common/parseQuery';

import './Filter.less';

interface FilterProps extends React.Props<JSX.Element> {
  onSubmit: (values: Object) => void;
}

export default class Filter extends React.Component<FilterProps,{}> {

  onSubmit(e: React.SyntheticEvent) {
    e.preventDefault();

    const query = e.target['s'].value;

    const values = parseQuery(query);

    this.props.onSubmit(values);
  }

  render() {
    return (
      <div className='Filter'>
        <form action='' method='post' onSubmit={this.onSubmit.bind(this)}>
          <p className='search-box'>
            <input type='search' className='Filter-query' name='s' />
            <input type='submit' className='button' value='Search commits' />
          </p>
        </form>
      </div>
    );
  }

}
