interface Diff {
  from: string;
  to: string;
  type: string; // Possible values: plain | binary
  chunks: Chunk[];
}

interface Chunk {
  lines: Line[];
}

interface Line {
  type: string; // Possible values: unchanged | added | removed
  content: string;
}
