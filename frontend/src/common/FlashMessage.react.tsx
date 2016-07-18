import * as React from 'react';
import * as classNames from 'classnames';

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
    this.state = {
      showDetails: false
    };
  }

  render() {
    const { code, message, details } = this.props;
    const { showDetails } = this.state;

    if (code === null) {
      return null;
    }

    const linkClassName = classNames({
      'FlashMessage-detailsLink-displayed': showDetails,
      'FlashMessage-detailsLink-hidden': !showDetails
    });

    return (
      <div className={code}>
        <p>
          {message} {' '}
          {details
            ? <a
                className={linkClassName}
                href='#'
                onClick={this.toggleDetails.bind(this)}
              >Details </a>
            : null}
        </p>
        {details && showDetails
          ? <p className='FlashMessage-details'>{details.toString()}</p>
          : null}
      </div>
    );
  }

  toggleDetails(e: React.MouseEvent) {
    e.preventDefault();
    this.setState({
      showDetails: !this.state.showDetails
    });
  }

}
