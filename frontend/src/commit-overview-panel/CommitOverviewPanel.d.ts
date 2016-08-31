/// <reference path='../common/Commits.d.ts' />

type ChangesByTypeAndAction = {
  [type: string]: {
    [action: string]: Change[];
  };
};

type PreprocessedLine = {
  key: string; 
  changes: Change[];
};

type CountOfDuplicateChanges = {
  [type: string]: {
    [action: string]: {
      [name: string]: number;
    };
  };
};
