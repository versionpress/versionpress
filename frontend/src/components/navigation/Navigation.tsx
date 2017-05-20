import * as React from 'react';
import { observer } from 'mobx-react';

import { clearSelection, filter, undoCommits } from '../../actions';
import BulkActionPanel from '../bulk-action-panel/BulkActionPanel';
import Filter from '../filter/Filter';
import { revertDialog } from '../portal/portal';

import { AppStore } from '../../stores/appStore';
import { NavigationStore } from '../../stores/navigationStore';

interface NavigationProps {
  appStore?: AppStore;
  navigationStore?: NavigationStore;
}

@observer(['appStore', 'navigationStore'])
export default class Navigation extends React.Component<NavigationProps, {}> {

  onFilterQueryChange = (query: string) => {
    const { navigationStore } = this.props;
    navigationStore.changeFilterQuery(query);
  }

  onBulkAction = (action: string) => {
    if (action === 'undo') {
      const { changesCount, hashes } = this.props.navigationStore;

      const title = (
        <span>Undo <em>{changesCount} {changesCount === 1 ? 'change' : 'changes'}</em>?</span>
      );

      revertDialog(title, () => undoCommits(hashes));
    }
  }

  render() {
    const { appStore, navigationStore } = this.props;
    const { enableActions } = appStore;
    const { query, changesCount } = navigationStore;

    return (
      <div className='tablenav top'>
        <Filter
          query={query}
          onQueryChange={this.onFilterQueryChange}
          onFilter={filter}
        />
        <BulkActionPanel
          enableActions={enableActions}
          onBulkAction={this.onBulkAction}
          onClearSelection={clearSelection}
          changesCount={changesCount}
        />
      </div>
    );
  }

}
