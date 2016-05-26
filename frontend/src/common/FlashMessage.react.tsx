/// <reference path='../../typings/typings.d.ts' />

import * as React from 'react';

import './FlashMessage.less';

interface FlashMessageProps extends React.Props<JSX.Element> {
  code: string;
  message: string;
  details?: string;
}

interface FlashMessageState {
  showDetails: boolean;
}

export default class FlashMessage extends React.Component<FlashMessageProps, FlashMessageState> {

  constructor() {
    super();
    this.state = { showDetails: false };
  }

  render() {
    if (this.props.code === null) {
      return null;
    }

    return (
      <div className={this.props.code}>
        <p>
          {this.props.message} {' '}
          {this.props.details
            ? <a
                className={'FlashMessage-detailsLink' + (this.state.showDetails ? '-displayed' : '-hidden')}
                href='#'
                onClick={this.toggleDetails.bind(this)}
              >Details </a>
            : null}
        </p>
        {this.props.details && this.state.showDetails
          ? <p className='FlashMessage-details'>{this.props.details.toString()}</p>
          : null}
      </div>
    );
  }

  toggleDetails(e: React.MouseEvent) {
    e.preventDefault();
    this.setState({ showDetails: !this.state.showDetails });
  }

}
