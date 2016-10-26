import * as React from 'react';
import { observer } from 'mobx-react';

import Details from './Details';
import ShowDetails from './ShowDetails';

import './FlashMessage.less';

interface FlashMessageProps {
  message: InfoMessage;
}

interface FlashMessageState {
  showDetails: boolean;
}

@observer
export default class FlashMessage extends React.Component<FlashMessageProps, FlashMessageState> {

  state = {
    showDetails: false,
  };

  onDetailsClick = () => {
    this.setState((prevState, props) => ({
      showDetails: !prevState.showDetails,
    }));
  };

  render() {
    const { code, message, details } = this.props.message;
    const { showDetails } = this.state;

    if (code === null) {
      return null;
    }

    return (
      <div className={code} style={{ flex: '1 0 100%', order: 2}}>
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
