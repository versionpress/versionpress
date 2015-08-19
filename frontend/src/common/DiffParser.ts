class DiffParser {
  public static parse(rawDiff: string): Diff[] {
    let lines = rawDiff.split(/\r\n|\r|\n/);
    let diffs: Diff[] = [];
    let diff: Diff = null;
    let collectedLines: string[] = [];

    for (let i = 0; i < lines.length; i++) {
      let line = lines[i];
      let nextLine = lines[i + 1];
      let lineMatch = line.match(/^---\s(\S+)/);
      let nextLineMatch = nextLine ? nextLine.match(/^\+\+\+\s(\S+)/) : null;

      if (lineMatch && nextLineMatch) { // Begin of new file
        if (diff !== null) {
          diff.chunks = DiffParser.parseFileDiff(collectedLines);
          diffs.push(diff);
        }

        diff = {from: lineMatch[1], to: nextLineMatch[1], type: 'plain', chunks: []};
        collectedLines = [];
        i++; // Skip the +++ line

      } else if (line.match(/^diff --git|index/)) {
        // Skip line
      } else if (line.match(/^Binary files .* differ/)) {
        let binaryFilesMatch = line.match(/^Binary files (.*) and (.*) differ/);
        diffs.push({from: binaryFilesMatch[1], to: binaryFilesMatch[2], chunks: [], type: 'binary'});
        collectedLines = [];
      } else {
        collectedLines.push(line);
      }
    }

    if (collectedLines.length > 0 && diff !== null) {
      diff.chunks = DiffParser.parseFileDiff(collectedLines);
      diffs.push(diff);
    }

    return diffs;
  }

  private static parseFileDiff(lines: string[]): Chunk[] {
    let chunks: Chunk[] = [];
    let chunk: Chunk = {lines: []};

    for (let i = 0; i < lines.length; i++) {
      let line = lines[i];
      if (line.match(/^@@/)) {
        chunk = {lines: []};
        chunks.push(chunk);
      }

      let match = line.match(/^([\+ -])(.*)/);
      if (match) {
        let type = 'unchanged';
        if (match[1] === '+') {
          type = 'added';
        } else if (match[1] === '-') {
          type = 'removed';
        }

        chunk.lines.push({type: type, content: match[2]});
      }

    }
    return chunks;
  }
}

export = DiffParser;
