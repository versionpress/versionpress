interface SearchConfig {
  [key: string]: SearchConfigItem;
}

interface SearchConfigItem {
  type: string;
  defaultHint?: string;
  sectionTitle?: string;
  content: SearchConfigItemContent[];
}

interface SearchConfigItemContent {
  value: string;
  label: string;
  fullText?: boolean;
  modifier?: boolean;
  section?: string;
  index?: number;
}

interface Token {
  key: string;
  modifier: string;
  value: string;
  type: string;
  length: number;
  negative: boolean;
}
