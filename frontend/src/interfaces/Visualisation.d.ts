interface CommitGraph {
  sha: string;
  environment: string;
  parents: string[];
}

interface CommitBranchRoute {
  branch: number;
  environment: string;
  from: number;
  to: number;
}

interface CommitNode {
  branch: number;
  environment: string;
  offset: number;
  routes: CommitBranchRoute[];
  sha: string;
}

interface Visualisation {
  upperRoutes: CommitBranchRoute[];
  lowerRoutes: CommitBranchRoute[];
  branch: number;
  environment: string;
  offset: number;
  isLastEnvCommit: boolean;
}
