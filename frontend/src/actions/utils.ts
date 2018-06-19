import * as request from 'superagent';
import * as Promise from 'core-js/es6/promise';
import * as WpApi from '../services/WpApi';

export function getErrorMessage(res: request.Response, err: any) {
  if (res) {
    const body = res.body;
    if ('code' in body && 'message' in body) {
      return body;
    }
  }
  console.error(err);
  return {
    code: 'error',
    message: 'VersionPress is not able to connect to WordPress site. Please try refreshing the page.',
    details: err,
  };
}

export function parsePageNumber(page: string | number) {
  return typeof page === 'number'
    ? page
    : (parseInt(page, 10) - 1) || 0;
}

export function getGitStatus() {
  return new Promise((resolve, reject) => {
    WpApi
      .get('git-status')
      .end((err, res: request.Response) => {
        const data = res.body.data as VpApi.GetGitStatusResponse;
        if (err) {
          reject(getErrorMessage(res, err));
        } else {
          resolve(data);
        }
      });
  });
}

export function getDiff(hash: string) {
  return new Promise((resolve, reject) => {
    WpApi
      .get('diff')
      .query(hash === '' ? {} : { commit: hash })
      .end((err, res: request.Response) => {
        const data = res.body.data as VpApi.GetDiffResponse;
        if (err) {
          reject(getErrorMessage(res, err));
        } else {
          resolve(data.diff);
        }
      });
  });
}

// Inspiration from https://github.com/jsdf/react-commits-graph/blob/170ab272020e1dc8b960ca6110f23c91524013f3/src/generate-graph-data.coffee
export function generateGraphData(commits: CommitGraph[]): CommitNode[] {
  let nodes: CommitNode[] = [];
  let branchIndex = 0;
  let reserve: number[] = [];
  let branches: { [key: string]: any } = {};
  let environments: { [key: string]: any } = {};

  const remove = (list: any[], item: any) => {
    list.splice(list.indexOf(item), 1);
    return list;
  };

  const getBranch = (sha: string) => {
    if (branches[sha] == null) {
      branches[sha] = branchIndex;
      reserve.push(branchIndex);
      branchIndex++;
    }

    return branches[sha];
  };

  commits.forEach(commit => {
    const branch = getBranch(commit.sha);
    const parentsCount = commit.parents.length;
    const offset = reserve.indexOf(branch);
    let routes: CommitBranchRoute[] = [];

    const insertToRoutes = (from: number, to: number, branch: number) => {
      routes.push({
        from,
        to,
        branch,
        environment: environments[branch],
      });
    };

    if (environments[branch] == null) {
      environments[branch] = commit.environment;
    }

    if (parentsCount === 1) {
      if (branches[commit.parents[0]] != null) {
        // Create branch
        let temp = reserve.slice(offset + 1);
        for (let i = 0; i < temp.length; i++) {
          insertToRoutes(i + offset + 1, i + offset + 1 - 1, temp[i]);
        }

        temp = reserve.slice(0, offset);
        for (let i = 0; i < temp.length; i++) {
          insertToRoutes(i, i, temp[i]);
        }

        remove(reserve, branch);
        insertToRoutes(offset, reserve.indexOf(branches[commit.parents[0]]), branch);
      } else {
        // Straight branch
        for (let i = 0; i < reserve.length; i++) {
          insertToRoutes(i, i, reserve[i]);
        }

        branches[commit.parents[0]] = branch;
      }
    } else if (parentsCount === 2) {
      // Merge branch
      branches[commit.parents[0]] = branch;

      for (let i = 0; i < reserve.length; i++) {
        insertToRoutes(i, i, reserve[i]);
      }

      const otherBranch = getBranch(commit.parents[1]);
      insertToRoutes(offset, reserve.indexOf(otherBranch), otherBranch);
    }

    nodes.push({
      branch,
      environment: commit.environment,
      offset,
      routes,
      sha: commit.sha,
    });
  });

  nodes.forEach(node => {
    node.routes.forEach(route => {
      route.environment = environments[route.branch];
    });
  });

  return nodes;
}
