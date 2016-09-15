import * as React from 'react';
import { observer } from 'mobx-react';

import BulkActionPanel from '../bulk-action-panel/BulkActionPanel';
import Filter from '../filter/Filter';
import { revertDialog } from '../portal/portal';

import store from '../../stores/navigationStore';

@observer
export default class Navigation extends React.Component<{}, {}> {

  undoCommits = (commits: string[]) => {
    store.undoCommits(commits);
  };

  onFilterQueryChange = (query: string) => {
    store.changeFilterQuery(query);
  };

  onFilter = () => {
    store.filter();
  };

  onClearSelection = () => {
    store.clearSelection();
  };

  onBulkAction = (action: string) => {
    if (action === 'undo') {
      const { changes, hashes } = store;

      const title = (
        <span>Undo <em>{changes} {changes === 1 ? 'change' : 'changes'}</em>?</span>
      );

      revertDialog(title, () => this.undoCommits(hashes));
    }
  };

  render() {
    const { query, enableActions, changes } = store;

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
