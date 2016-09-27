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
      .query(hash === '' ? null : { commit: hash })
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

/*
 Generate graph data.

 :param commits: a list of commit, which should have
 `sha`, `parents` properties.
 :returns: data nodes, a json list of
 [
   sha,
   [offset, branch], //dot
   [
     [from, to, branch],  // route 1
     [from, to, branch],  // route 2
     [from, to, branch],
   ]  // routes
 ],  // node
 */
export function generateGraphData(commits: CommitGraph[]): CommitNode[] {
  let
    nodes = [],
    branchIndex = 0,
    reserve = [],
    branches = {},
    environments = {};

  const remove = (list, item) => {
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
    let routes = [];

    const insertToRoutes = (from, to, branch) => {
      routes.push({
        from: from,
        to: to,
        branch: branch,
        environment: environments[branch]
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
      branch: branch,
      environment: commit.environment,
      offset: offset,
      routes: routes,
      sha: commit.sha
    });
  });

  nodes.forEach(node => {
    node.routes.forEach(route => {
      route.environment = environments[route.branch];
    });
  });

  return nodes;
}
