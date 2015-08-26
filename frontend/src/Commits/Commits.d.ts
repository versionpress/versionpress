interface Commit {
  message: string;
  date: string;
  hash: string;
  canUndo: boolean;
  canRollback: boolean;
  isEnabled: boolean;
  isInitial: boolean;
  changes: Change[];
}

interface Change {
  type: string;
  action: string;
  name: string;
}
