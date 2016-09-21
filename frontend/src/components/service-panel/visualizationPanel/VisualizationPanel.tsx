import * as React from 'react';
import * as classNames from 'classnames';

import { getGitBranchColor } from '../../../services/GitBranchColorProvider';
import BranchCommit from "../../../stores/BranchCommit";

interface VisualizationPanelProps {
  isVisible: boolean;
  environments: string[];
  visualization: BranchCommit[][];
}

export default class VisualizationPanel extends React.Component<VisualizationPanelProps, {}> {

  renderBranchCommits() {
    const { visualization } = this.props;

    return visualization.map((branchCommits: BranchCommit[], index) => {
      const activeBranchCommit = branchCommits.find(commit => commit.commit !== null);

      return (
        <tr key={activeBranchCommit.commit.hash}>
          {branchCommits.map((branchCommit, otherIndex) => {
            if (!branchCommit) {
              return (
                <td
                  key={`${index}-${otherIndex}-empty`}
                  colSpan={3}
                />
              )
            }

            const { commit, environment } = branchCommit;

            if (!commit) {
              return [
                <td
                  key={`${index}-${otherIndex}-empty--${environment}`}
                  width={10}
                  style={{ backgroundColor: getGitBranchColor(getGitBranchColor(environment)) }}
                />,
                <td
                  key={`${index}-${otherIndex}-empty-${environment}`}
                  colSpan={2}
                />
              ]
            }

            return [
              <td
                key={`${index}-${otherIndex}-empty--${environment}`}
                width={10}
                style={{ backgroundColor: getGitBranchColor(getGitBranchColor(environment)) }}
              />,
              <td
                key={`${index}-${otherIndex}-empty-${environment}`}
                colSpan={2}
                style={{ paddingLeft: 10, paddingRight: '5%'}}
                width='200'
              >
                {branchCommit.checkoutChildren && <i>{`checkout to ${branchCommit.checkoutChildren}`}</i>}
                {branchCommit.checkoutChildren && <br />}
                {branchCommit.mergeTo && <i>{`merge to ${branchCommit.mergeTo}`}</i>}
                {branchCommit.mergeTo && <br />}
                {`${commit.message.slice(0, 8)}${commit.message.length > 8 ? '...' : ''}`}
                {branchCommit.checkoutFrom && <br />}
                {branchCommit.checkoutFrom && <i>{`checkout from ${branchCommit.checkoutFrom}`}</i>}
                {branchCommit.mergeParents && <br />}
                {branchCommit.mergeParents && <i>{`merge from ${branchCommit.mergeParents}`}</i>}
              </td>
            ]
          })}
        </tr>
      )
      });
  }

  render() {
    const { isVisible, environments } = this.props;

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
                {environments.map(env => (
                  <th
                    colSpan={3}
                    key={env}
                  >
                    {env}
                  </th>
                ))}
              </tr>
              </thead>
              <tbody>
                {this.renderBranchCommits()}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    );
  }

}
