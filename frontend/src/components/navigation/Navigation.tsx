import * as React from 'react';
import { observer } from 'mobx-react';

import BulkActionPanel from '../bulk-action-panel/BulkActionPanel';
import Filter from '../filter/Filter';
import { revertDialog } from '../portal/portal';

import { NavigationStore } from '../../stores/navigationStore';

interface NavigationProps {
  navigationStore?: NavigationStore;
}

@observer(['navigationStore'])
export default class Navigation extends React.Component<NavigationProps, {}> {

  undoCommits = (commits: string[]) => {
    const { navigationStore } = this.props;
    navigationStore.undoCommits(commits);
  };

  onFilterQueryChange = (query: string) => {
    const { navigationStore } = this.props;
    navigationStore.changeFilterQuery(query);
  };

  onFilter = () => {
    const { navigationStore } = this.props;
    navigationStore.filter();
  };

  onClearSelection = () => {
    const { navigationStore } = this.props;
    navigationStore.clearSelection();
  };

  onBulkAction = (action: string) => {
    if (action === 'undo') {
      const { changes, hashes } = this.props.navigationStore;

      const title = (
        <span>Undo <em>{changes} {changes === 1 ? 'change' : 'changes'}</em>?</span>
      );

      revertDialog(title, () => this.undoCommits(hashes));
    }
  };

  render() {
    const { query, enableActions, changes } = this.props.navigationStore;

    return (
      <div className='tablenav top'>
        <Filter
          query={query}
          onQueryChange={this.onFilterQueryChange}
          onFilter={this.onFilter}
        />
        <BulkActionPanel
          enableActions={enableActions}
          onBulkAction={this.onBulkAction}
          onClearSelection={this.onClearSelection}
          changes={changes}
        />
      </div>
    );
  }

}
