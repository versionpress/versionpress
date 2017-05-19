declare namespace VpApi {
  interface GetCommitsResponse {
    pages: number[];
    commits: Commit[];
  }

  type UndoCommitsResponse = boolean;

  type RollbackToCommitResponse = boolean;

  type CanRevertResponse = boolean;

  interface GetDiffResponse {
    diff: string;
  }

  type DisplayWelcomePanelResponse = boolean;

  interface ShouldUpdateResponse {
    update: boolean;
    cleanWorkingDirectory: boolean;
  }

  type GetGitStatusResponse = string[][];
}
