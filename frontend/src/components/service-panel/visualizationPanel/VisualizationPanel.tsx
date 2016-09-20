import * as React from 'react';
import * as classNames from 'classnames';

interface VisualizationPanelProps {
  isVisible: boolean;
  commits: Commit[];
}

const VisualizationPanel: React.StatelessComponent<VisualizationPanelProps> = ({ isVisible, commits }) => {
  const wrapperClassName = classNames({
    'ServicePanel-wrapper': true,
    'ServicePanel-wrapper--hide': !isVisible,
  });

  return (
    <div className={wrapperClassName}>
      <div className='ServicePanel welcome-panel'>
        <div className='ServicePanel-inner'>
          {commits.map((commit: Commit) => (
            <div key={commit.hash}>`${commit.environment} - ${commit.message}`</div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default VisualizationPanel;
