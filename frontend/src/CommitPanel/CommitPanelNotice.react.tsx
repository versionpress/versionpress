import * as React from 'react';

interface CommitPanelNoticeProps extends React.Props<JSX.Element> {
  onDetailsLevelChanged: (detailsLevel) => any;
  detailsLevel: string;
}

export default class CommitPanelNotice extends React.Component<CommitPanelNoticeProps, {}> {

  render() {
    return (
      <p>
        You have {' '}
        <a
          href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files' target='_blank'
        >uncommitted changes</a>
        {' '} in your WordPress directory. {' '}
        <a className='CommitPanel-notice-toggle' onClick={() => this.toggleDetails()}>
          Click here to {this.props.detailsLevel === 'none' ? 'show' : 'hide'} changes.
        </a>
      </p>
    );
  }

  private toggleDetails() {
    this.changeDetailsLevel(this.props.detailsLevel === 'none' ? 'overview' : 'none');
  }

  private changeDetailsLevel(detailsLevel) {
    this.props.onDetailsLevelChanged(detailsLevel);
  }

}
