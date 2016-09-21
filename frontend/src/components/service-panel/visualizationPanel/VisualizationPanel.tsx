import * as React from 'react';
import * as classNames from 'classnames';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';
import BranchCommit from "../../../stores/BranchCommit";

interface VisualizationPanelProps {
  isVisible: boolean;
  commits: Commit[];
  environments: string[];
  visualization: BranchCommit[][];
}

const VisualizationPanel: React.StatelessComponent<VisualizationPanelProps> = ({
  isVisible, commits, environments, visualization
}) => {
  const wrapperClassName = classNames({
    'ServicePanel-wrapper': true,
    'ServicePanel-wrapper--hide': !isVisible,
  });

  console.log(visualization);

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
                  style={{
                    backgroundColor: getGitBranchColor(getGitBranchColor(commit.environment)),
                    border: 0
                  }}
                />
                <td
                  colSpan={2}
                  style={{ paddingLeft: 10}}
                >
                  {`${commit.message.slice(0, 8)}${commit.message.length > 8 ? '...' : ''}`}
                </td>
                {environments[0] === commit.environment && <td colSpan={3} /> }
                {commit.isMerge && console.log("merge", commit)}
                {commit.isInitial && console.log("init", commit)}
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
