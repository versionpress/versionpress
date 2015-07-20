interface Commit {
  message: string;
  date: string;
  hash: string;
  canUndo: boolean;
  canRollback: boolean;
  isEnabled: boolean;
}
