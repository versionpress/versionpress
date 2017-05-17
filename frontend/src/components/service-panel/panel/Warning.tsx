import * as React from 'react';

const Warning: React.StatelessComponent<{}> = () => (
  <p className='ServicePanel-warning'>
    Currently, VersionPress is a {' '}
    <a href='http://docs.versionpress.net/en/getting-started/about-eap'>
      <strong>Developer Preview</strong>
    </a>.<br />
    As such, it might not fully support certain workflows, 3rd party plugins, hosts etc.
  </p>
);

export default Warning;
