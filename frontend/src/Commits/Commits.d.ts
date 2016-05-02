interface Commit {
  message: string;
  date: string;
  hash: string;
  canUndo: boolean;
  canRollback: boolean;
  isEnabled: boolean;
  isInitial: boolean;
  isMerge: boolean;
  environment: string;
  changes: Change[];
  author: {
    name: string;
    email: string;
    avatar: string;
  };
}

interface Change {
  type: string;
  action: string;
  name: string;
  tags: any;
}
