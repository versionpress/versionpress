import * as React from 'react';

interface FlashMessageProps extends React.Props<JSX.Element> {
  code: string;
  message: string;
}

export default class FlashMessage extends React.Component<FlashMessageProps, {}> {

  render() {
    if (this.props.code === null) {
      return null;
    }

    return (
      <div className={this.props.code}>
        <p>{this.props.message}</p>
      </div>
    );
  }

}
