<?php

namespace VersionPress\Utils\Serialization;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

class SerializedDataToIniConverter {

    private $serializedMarker;

    public function __construct($serializedMarker) {
        $this->serializedMarker = $serializedMarker;
    }

    public function toIniLines($key, $serializedData) {
        $unserializedData = unserialize($serializedData);
        $iniLines = self::serializeDataToIni($key, $unserializedData);

        // Add marker
        $iniLines[0] = StringUtils::replaceFirst(' = ', " = {$this->serializedMarker} ", $iniLines[0]);

        return $iniLines;
    }

    /**
     * Restores the PHP-serialized value from INI.
     * For examples see the tests.
     *
     * @param string $key The original top-level key.
     * @param string[] $lines Lines related to the $key. Hierarchical structures are saved as multiple lines.
     * @return string Original result of PHP serialization.
     */
    public function fromIniLines($key, $lines) {
        $value = substr($lines[$key], strlen($this->serializedMarker) + 1); // + space
        unset($lines[$key]);

        return self::convertValueToSerializedString($value, $lines);
    }

    /**
     * Transforms PHP primitives, arrays, objects etc. to INI. Complex structures like arrays and objects
     * can take multiple lines (every scalar value takes one line).
     *
     * @param string $key Key used in INI.
     * @param mixed $value Value to serialize
     * @return array
     */
    private function serializeDataToIni($key, $value) {
        if (is_numeric($value) || is_string($value)) {
            return [$key . ' = ' . self::primitiveToEscapedString($value)];

        } else if (is_bool($value)) {
            return [$key . ' = <boolean> ' . ($value ? 'true' : 'false')];

        } else if (is_array($value)) {
            return $this->serializeArrayToIni($key, $value);

        } else if (is_object($value)) {
            return $this->serializeObjectToIni($key, $value);

        } else if (is_null($value)) {
            return [$key . ' = <null>'];
        }

        return [];
    }

    private function serializeArrayToIni($key, $value) {
        $lines = [$key . ' = <array>'];
        foreach ($value as $arrayKey => $arrayValue) {
            $subkey = $key . '[' . self::primitiveToEscapedString($arrayKey) . ']';
            $lines = array_merge($lines, self::serializeDataToIni($subkey, $arrayValue));
        }
        return $lines;
    }

    private function serializeObjectToIni($key, $value) {
        $lines = [$key . ' = <' . get_class($value) . '>'];
        $reflection = new \ReflectionObject($value);
        $properties = $reflection->getProperties();

        if (method_exists($value, '__sleep')) {
            $propertyNames = $value->__sleep();
        } else {
            $propertyNames = array_map(function (\ReflectionProperty $property) {
                return $property->getName();
            }, $properties);
        }

        foreach ($propertyNames as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $accesibilityFlag = $property->isPrivate() ? '-' : ($property->isProtected() ? '*' : '');

            $propertyValue = $property->getValue($value);
            $subkey = $key . '["' . $accesibilityFlag . $property->getName() . '"]';
            $lines = array_merge($lines, self::serializeDataToIni($subkey, $propertyValue));
        }

        return $lines;
    }

    private static function primitiveToEscapedString($value) {
        if (is_numeric($value)) {
            return (string)$value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return '"' . str_replace('"', '\"', $value) . '"';
    }

    /**
     * Converts single value (string, number, array, object, ...) to PHP-like serialized string.
     * Takes the top-level value + lines related to it.
     * It's called recursively for hierarchical structures (array, object).
     *
     * @param $value
     * @param array $relatedKeys
     * @return string
     */
    private static function convertValueToSerializedString($value, $relatedKeys = []) {
        $type = null; // string or number

        // https://regex101.com/r/gJ1oF2/1
        if (preg_match('/^<([\w\d\\\\]+)> ?(.*)/', $value, $matches)) {
            $type = $matches[1]; // detect type and value from eg. `<boolean> false`
            $value = $matches[2];
        }

        if (is_numeric($value)) {
            return (Strings::contains($value, '.') ? 'd' : 'i') . ':' . $value . ';';
        }

        if ($type === 'boolean') {
            return 'b:' . ($value === 'false' ? 0 : 1) . ';';
        }

        if ($type === 'array') {
            return self::convertArrayToSerializedString($relatedKeys);
        }

        if (class_exists($type)) {
            return self::convertObjectToSerializedString($type, $relatedKeys);
        }

        if ($type === 'null') {
            return 'N;';
        }

        if (Strings::startsWith($value, '"')) { // plain serialized strings are in quotes because of `<<<serialized>>> "string"`
            $value = preg_replace('/^"(.*)"$/', '$1', $value);
        }

        return 's:' . strlen($value) . ':' . self::primitiveToEscapedString($value) . ';';
    }

    /**
     * Converts array items saved in INI to PHP-like serialized string.
     *
     * @param $relatedKeys
     * @return string
     */
    private static function convertArrayToSerializedString($relatedKeys) {
        $subItems = self::getSubItems($relatedKeys);
        return 'a:' . count($subItems) . ':{' . join('', $subItems) . '}';
    }

    /**
     * Converts object saved in INI to PHP-like serialized string.
     * Protected fields are prefixed with "*" in INI. PHP serialization saves them with prefix \0*\0 (where \0 is a NULL byte).
     * Private fields are prefixed with "-". PHP saves them with prefix \0<full class name>\0.
     *
     * @param $type
     * @param $relatedKeys
     * @return string
     */
    private static function convertObjectToSerializedString($type, $relatedKeys) {
        $subItems = self::getSubItems($relatedKeys, function ($subkey) use ($type) {
            if (strpos($subkey, '*') === 1) {
                return "\"\0*\0" . substr($subkey, 2);
            }

            if (strpos($subkey, '-') === 1) {
                return "\"\0{$type}\0" . substr($subkey, 2);
            }

            return $subkey;
        });

        return 'O:' . strlen($type) . ':"' . $type . '":' . count($subItems) . ':{' . join('', $subItems) . '}';
    }

    /**
     * Takes lines of INI representing array items or class fields and returns them as PHP-like serialized string.
     *
     * Finds original key and value at every line  (eg. line `[ 'some_data[0]' => 'some value' ]` contains key `0`
     * and value `some value`) and converts them to the PHP-like serialized string (in this case `i:0;s:10:"some value";`.
     *
     * @param $relatedKeys
     * @param callable|null $subkeyTransformFn
     * @return array
     */
    private static function getSubItems($relatedKeys, $subkeyTransformFn = null) {
        $items = [];
        foreach ($relatedKeys as $relatedKey => $valueOfRelatedKey) {

            $indexAfterFirstOpeningBracket = strpos($relatedKey, '[') + 1;
            $indexOfFirstClosingBracket = strpos($relatedKey, ']');
            $keyLength = $indexOfFirstClosingBracket - $indexAfterFirstOpeningBracket;

            $subkey = substr($relatedKey, $indexAfterFirstOpeningBracket, $keyLength);

            if (is_callable($subkeyTransformFn)) {
                $subkey = $subkeyTransformFn($subkey);
            }

            if (strpos($relatedKey, '[', $indexOfFirstClosingBracket) === false) {
                $relatedKeysOfSubItem = self::findRelatedKeys($relatedKeys, $relatedKey);
                $items[] = self::convertValueToSerializedString($subkey) . self::convertValueToSerializedString($valueOfRelatedKey, $relatedKeysOfSubItem);
            }
        }
        return $items;
    }

    /**
     * Finds array items with prefix $commonKey and cuts the prefix off.
     * Useful for finding items of array / class fields.
     *
     * Example:
     *
     *  For common key "some_key"
     *  and array [ "some_key" => "<array>", "some_key[0]" => "item 1", "some_key[1]" => "item 2" ]
     *  it returns [ "[0]" => "item 1", "[1]" => "item 2" ].
     *
     *
     * @param $maybeRelatedKeys
     * @param $commonKey
     * @return array
     */
    private static function findRelatedKeys($maybeRelatedKeys, $commonKey) {
        $rel = [];
        $lengthOfCommonPart = strlen($commonKey);

        foreach ($maybeRelatedKeys as $key => $value) {
            if (Strings::startsWith($key, $commonKey) && $key !== $commonKey) {
                $rel[substr($key, $lengthOfCommonPart)] = $value;
            }
        }
        return $rel;
    }
}
