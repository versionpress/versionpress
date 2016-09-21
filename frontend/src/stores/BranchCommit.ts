export default class BranchCommit {
  commit: Commit = null;
  isEmpty: boolean = false;
  environment: string = '';
  isMerge: boolean = false;
  isStart: boolean = false;
  isCheckout: boolean = false;
  isEnd: boolean = false;


  constructor(
    commit: Commit,
    isEmpty = false,
    environment = '',
    isStart = false,
    isCheckout = false,
    isEnd = false
  ) {
    this.commit = commit;
    this.isEmpty = isEmpty;
    this.environment = commit ? commit.environment : environment;
    this.isMerge = commit ? commit.isMerge : false;
    this.isStart = isStart;
    this.isCheckout = isCheckout;
    this.isEnd = isEnd;
  }
}
