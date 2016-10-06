/// <reference path='../Search.d.ts' />

interface Adapter {
  autoComplete(token: Token): SearchConfigItemContent;
  getDefaultHint(): string;
  getHints(token: Token): SearchConfigItemContent[];
  isValueValid(value: string): boolean;
  serialize(item: any): string;
  deserialize(value: string): SearchConfigItemContent | string;
}
