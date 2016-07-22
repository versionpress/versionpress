import * as React from 'react';

import './Filter.less';

interface FilterProps extends React.Props<JSX.Element> {
  onSubmit: (values: string) => void;
}

export default class Filter extends React.Component<FilterProps, {}> {

  private isSubmitted = false;

  componentDidMount() {
    (this.refs['search'] as HTMLElement).addEventListener('search', (e) => this.onSearch(e));
  }

  componentWillUnmount() {
    (this.refs['search'] as HTMLElement).removeEventListener('search', (e) => this.onSearch(e));
  }

  onSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Prevent form submit multiple times via both onSearch and onSubmit
    // (Happens only with empty input value and pressing Enter)
    this.isSubmitted = true;
    setTimeout(() => { this.isSubmitted = false; }, 10);

    const query = e.target['s'].value;

    this.props.onSubmit(query);
  }

  onSearch(e: Event) {
    const input = (e.target as HTMLInputElement);
    if (input.value === '' && !this.isSubmitted) {
      (input.form as HTMLFormElement).dispatchEvent(new Event('submit'));
    }
  }

  render() {
    return (
      <div className='Filter'>
        <form action='' method='post' onSubmit={this.onSubmit}>
          <p className='search-box'>
            <input type='search' className='Filter-query' name='s' ref='search' />
            <input type='submit' className='button' value='Search' />
          </p>
        </form>
      </div>
    );
  }

}
