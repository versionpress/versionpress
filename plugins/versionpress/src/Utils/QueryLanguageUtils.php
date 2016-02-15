<?php

namespace VersionPress\Utils;

class QueryLanguageUtils {

    const VALUE_WILDCARD = 'VALUE_WILDCARD';
    const VALUE_STRING = 'VALUE_STRING';

    // https://regex101.com/r/wT6zG3/4 (query language)
    private static $queryRegex = "/(-)?(?:(\\S+):\\s*)?(?:'((?:[^'\\\\]|\\\\.)*)'|\"((?:[^\"\\\\]|\\\\.)*)\"|(\\S+))/";

    // https://regex101.com/r/pL2zA2/3 (support for * wildcard)
    private static $valueWildcardRegex = "/(?:\\\\\\\\)|(?:\\\\\\*)|(?:\\*)|(?:[^\\\\\\*]+)/";

    /**
     * Transforms queries into arrays for easier manipulation.
     * Example of transformation of one query (method works with list):
     *  query = some_field: value other_field: other_value
     *  array = ['some_field' => 'value', 'other_field' => 'other_value']
     *
     * @param $queries
     * @return array
     */
    public static function createRulesFromQueries($queries) {
        $rules = array();
        foreach ($queries as $query) {
            $possibleValues = array();

            preg_match_all(self::$queryRegex, $query, $matches);
            $isValidRule = count($matches[0]) > 0;
            if (!$isValidRule) {
                continue;
            }

            $keys = $matches[2];

            /* value can be in 3rd, 4th or 5th group
             *
             * 3rd group => value is in single quotes
             * 4th group => value is in double quotes
             * 5th group => value is without quotes
             *
             */
            $possibleValues[] = $matches[3];
            $possibleValues[] = $matches[4];
            $possibleValues[] = $matches[5];

            // we need to join all groups together
            $ruleParts = array();
            foreach ($possibleValues as $possibleValue) {
                foreach ($possibleValue as $index => $value) {
                    if ($value !== '') {
                        $ruleParts[$index] = $value;
                    }
                }
            }

            ksort($ruleParts);
            $rules[] = array_combine($keys, $ruleParts);
        }
        return $rules;
    }

    /**
     * Tests if entity satisfies at least one of given rules.
     *
     * @param $entity
     * @param $rules
     * @return bool
     */
    public static function entityMatchesSomeRule($entity, $rules) {
        return ArrayUtils::any($rules, function ($rule) use ($entity) {
            return ArrayUtils::all($rule, function ($value, $field) use ($entity) { // check all parts of rule
                if (!isset($entity[$field])) {
                    return false;
                }

                $valueTokens = QueryLanguageUtils::tokenizeValue($value);
                $isWildcard = QueryLanguageUtils::tokensContainWildcard($valueTokens);

                if ($isWildcard && preg_match(QueryLanguageUtils::tokensToRegex($valueTokens), $entity[$field])) {
                    return true;
                } elseif ($entity[$field] == $value) {
                    return true;
                }

                return false;
            });
        });
    }

    /**
     * Converts rule (array) to isolated (enclosed in brackets) part of SQL restriction.
     *
     * Example:
     *  rule = ['field' => 'value', 'other_field' => 'with_prefix*']
     *  output = (`field` = "value" AND `other_field` LIKE "with_prefix%")
     *
     * @param $rule array
     * @return string
     */
    public static function createSqlRestrictionFromRule($rule) {
        $restrictionParts = array();

        foreach ($rule as $field => $value) {
            $valueTokens = self::tokenizeValue($value);
            $isWildcard = self::tokensContainWildcard($valueTokens);
            $searchedValue = self::tokensToSqlString($valueTokens);

            $operator = $isWildcard ? 'LIKE' : '=';

            $escapedValue = str_replace('"', '\"', $searchedValue);

            if ($isWildcard) {
                $escapedValue = str_replace('_', '\_', $escapedValue);
            }

            $restrictionPart = sprintf('`%s` %s "%s"', $field, $operator, $escapedValue);
            $restrictionParts[] = $restrictionPart;
        }

        return sprintf('(%s)', join(' AND ', $restrictionParts));
    }

    /**
     * Splits value into tokens.
     * Tokens:
     *   *    => VALUE_WILDCARD,
     *   else => VALUE_STRING
     *
     * @param $value
     * @return array
     */
    private static function tokenizeValue($value) {
        preg_match_all(self::$valueWildcardRegex, $value, $matches);
        $tokens = array();

        foreach ($matches[0] as $valuePart) {
            if ($valuePart === '*') {
                $tokens[] = array(
                    'type' => self::VALUE_WILDCARD
                );
            } else if ($valuePart === '\*') {
                $tokens[] = array(
                    'type' => self::VALUE_STRING,
                    'value' => '*',
                );
            } else if ($valuePart === '\\\\') {
                $tokens[] = array(
                    'type' => self::VALUE_STRING,
                    'value' => '\\',
                );
            } else {
                $tokens[] = array(
                    'type' => self::VALUE_STRING,
                    'value' => $valuePart,
                );
            }
        }

        return $tokens;
    }

    /**
     * Converts tokens to regular expression.
     * Wildcard is replaced by '.*'.
     *
     * For tokens from value 'prefix*' it returns '/^prefix.*$/'.
     *
     * @param $valueTokens
     * @return string
     */
    private static function tokensToRegex($valueTokens) {
        $regexDelimiter = '/';
        $regexFromValue = join('', array_map(function ($token) use ($regexDelimiter) {
            return QueryLanguageUtils::tokenToRegex($token, $regexDelimiter);
        }, $valueTokens));

        return sprintf('%s^%s$%s', $regexDelimiter, $regexFromValue, $regexDelimiter);
    }

    private static function tokenToRegex($token, $delimiter) {
        return $token['type'] === self::VALUE_WILDCARD ? '.*' : preg_quote($token['value'], $delimiter);
    }

    private static function tokensToSqlString($valueTokens) {
        return join('', array_map(array('VersionPress\Utils\QueryLanguageUtils', 'tokenToSqlString'), $valueTokens));
    }

    private static function tokenToSqlString($token) {
        return $token['type'] === self::VALUE_WILDCARD ? '%' : $token['value'];
    }

    private static function tokensContainWildcard($valueTokens) {
        return array_search(self::VALUE_WILDCARD, array_column($valueTokens, 'type')) !== false;
    }
}
