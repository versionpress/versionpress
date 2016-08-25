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
  author: Author;
}

interface Change {
  type: string;
  action: string;
  name: string;
  tags: any;
}

interface Author {
  name: string;
  email: string;
  avatar: string;
}
