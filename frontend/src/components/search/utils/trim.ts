export const IN_QUOTES_REGEXP = /^(".*")|('.*')$/;

export function trim(value: string) {
  value = value.trim();
  if (IN_QUOTES_REGEXP.test(value)) {
    value = value.slice(1, -1);
  }

  return value;
}
