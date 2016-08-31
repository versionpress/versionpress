import * as React from 'react';

import Details from './Details';
import ShowDetails from './ShowDetails';

import './FlashMessage.less';

interface FlashMessageProps {
  message: InfoMessage;
}

interface FlashMessageState {
  showDetails: boolean;
}

export default class FlashMessage extends React.Component<FlashMessageProps, FlashMessageState> {

  state = {
    showDetails: false,
  };

  onDetailsClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.setState({
      showDetails: !this.state.showDetails,
    });
  };

  render() {
    const { code, message, details } = this.props.message;
    const { showDetails } = this.state;

    if (code === null) {
      return null;
    }

    return (
      <div className={code}>
        <p>
          {message} {' '}
          {details &&
            <ShowDetails
              isActive={showDetails}
              onClick={this.onDetailsClick}
            />
          }
        </p>
        {(details && showDetails) &&
          <Details text={details} />
        }
      </div>
    );
  }

}
