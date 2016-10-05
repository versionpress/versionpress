import * as React from 'react';
import { observer } from 'mobx-react';
import { observable } from 'mobx';

import Details from './Details';
import ShowDetails from './ShowDetails';

import './FlashMessage.less';

interface FlashMessageProps {
  message: InfoMessage;
}

@observer
export default class FlashMessage extends React.Component<FlashMessageProps, {}> {

  @observable showDetails: boolean = false;

  onDetailsClick = (e: React.MouseEvent) => {
    e.preventDefault();

    this.showDetails = !this.showDetails;
  };

  render() {
    const { code, message, details } = this.props.message;

    if (code === null) {
      return null;
    }

    return (
      <div className={code}>
        <p>
          {message} {' '}
          {details &&
            <ShowDetails
              isActive={this.showDetails}
              onClick={this.onDetailsClick}
            />
          }
        </p>
        {(details && this.showDetails) &&
          <Details text={details} />
        }
      </div>
    );
  }

}
