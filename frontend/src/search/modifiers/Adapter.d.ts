/// <reference path='../Search.d.ts' />

interface Adapter {
  getDefaultHint(): string;
  getHints(token: Token): SearchConfigItemContent[];
  isValueValid(value: string): boolean;
  serialize(item: SearchConfigItemContent): string;
  deserialize(value: string): SearchConfigItemContent | string;
}
