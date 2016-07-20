import * as React from 'react';

interface CommitPanelNoticeProps extends React.Props<JSX.Element> {
  onDetailsLevelChange: (detailsLevel: string) => any;
  detailsLevel: string;
}

export default class CommitPanelNotice extends React.Component<CommitPanelNoticeProps, {}> {

  onDetailsClick = (e: React.MouseEvent) => {
    e.preventDefault();

    const { detailsLevel, onDetailsLevelChange } = this.props;
    onDetailsLevelChange(detailsLevel === 'none' ? 'overview' : 'none');
  }

  render() {
    return (
      <p>
        You have {' '}
        <a
          href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files' target='_blank'
        >uncommitted changes</a>
        {' '} in your WordPress directory. {' '}
        <a className='CommitPanel-notice-toggle' onClick={this.onDetailsClick}>
          Click here to {this.props.detailsLevel === 'none' ? 'show' : 'hide'} changes.
        </a>
      </p>
    );
  }

}
