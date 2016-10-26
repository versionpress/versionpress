import * as React from 'react';
import { observer } from 'mobx-react';

import CommitOverviewPanel from '../../commit-overview-panel/CommitOverviewPanel';

interface OverviewProps {
  commit: Commit;
}

const Overview: React.StatelessComponent<OverviewProps> = ({ commit }) => (
  <CommitOverviewPanel commit={commit} />
);

export default observer(Overview);
