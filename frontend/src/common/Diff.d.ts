interface Diff {
  from: string;
  to: string;
  type: string; // plain | binary
  chunks: Chunk[];
}

interface Chunk {
  lines: Line[];
}

interface Line {
  type: string; // unchanged | added | removed
  content: string;
}