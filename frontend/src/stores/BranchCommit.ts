interface BranchCommitParams {
  environment?: string;
  isMerge?: boolean;
  isCheckout?: boolean;
  isEnd?: boolean;
  mergeParents?: string[];
  mergeTo?: string[];
  checkoutChildren?: string[];
  checkoutFrom?: string;
}

export default class BranchCommit {
  commit: Commit = null;
  environment: string = null;
  isMerge: boolean = false;
  isCheckout: boolean = false;
  isEnd: boolean = false;
  mergeParents: string[];
  mergeTo: string[];
  checkoutChildren: string[];
  checkoutFrom: string;

  constructor(commit: Commit, params: BranchCommitParams = {}) {
    const {
      environment = null,
      isCheckout = false,
      isEnd = false,
      mergeParents = null,
      mergeTo = null,
      checkoutChildren = null,
      checkoutFrom = null,
    } = params;

    this.commit = commit;
    this.environment = commit ? commit.environment : environment;
    this.isMerge = commit ? commit.isMerge : false;
    this.isCheckout = isCheckout;
    this.isEnd = isEnd;
    this.mergeParents = mergeParents;
    this.mergeTo = mergeTo;
    this.checkoutChildren = checkoutChildren;
    this.checkoutFrom = checkoutFrom;
  }

  get isEmpty() {
    return !this.commit;
  }

  get isStart() {
    return !!this.checkoutFrom;
  }
}
