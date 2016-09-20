import * as React from 'react';
import * as classNames from 'classnames';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';

interface VisualizationPanelProps {
  isVisible: boolean;
  commits: Commit[];
  environments: string[];
}

const VisualizationPanel: React.StatelessComponent<VisualizationPanelProps> = ({
  isVisible, commits, environments
}) => {
  const wrapperClassName = classNames({
    'ServicePanel-wrapper': true,
    'ServicePanel-wrapper--hide': !isVisible,
  });

  return (
    <div className={wrapperClassName}>
      <div className='ServicePanel welcome-panel'>
        <div className='ServicePanel-inner'>
          <h1>{`Environments: ${environments.length}`}</h1>
          <table>
            <thead>
              <tr>
                {environments.map(env => <th colSpan={3} key={env}>{env}</th>)}
              </tr>
            </thead>
            <tbody>
            {commits.map((commit: Commit) => (
              <tr key={commit.hash}>
                {environments[0] !== commit.environment && <td colSpan={3} /> }
                <td
                  width={10}
                  style={{ backgroundColor: getGitBranchColor(getGitBranchColor(commit.environment))}}
                />
                <td colSpan={2}>
                  {`${commit.message.slice(0, 15)}${commit.message.length > 15 ? '...' : ''}`}
                </td>
                {environments[0] === commit.environment && <td colSpan={3} /> }
              </tr>
            ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default VisualizationPanel;
