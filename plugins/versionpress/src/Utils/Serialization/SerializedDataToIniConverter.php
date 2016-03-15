<?php

namespace VersionPress\Utils\Serialization;

use Nette\Utils\Strings;

class SerializedDataToIniConverter {

    private $serializedMarker;

    public function __construct($serializedMarker) {
        $this->serializedMarker = $serializedMarker;
    }

    public function toIniLines($key, $serializedData) {
        $unserializedData = unserialize($serializedData);
        return self::serializePhpSerializedValue($key, $unserializedData);
    }

    private function serializePhpSerializedValue($key, $value, $isRoot = true) {
        $line = $key . " = ";

        if ($isRoot) {
            $line .= $this->serializedMarker . ' ';
        }

        $subitems = [];

        if (is_numeric($value) || is_string($value)) {
            $line .= self::serializePlainValue($value);
        } else if (is_bool($value)) {
            $line .= '<boolean> ' . ($value ? 'true' : 'false');
        } else if (is_array($value)) {
            $line .= '<array>';
            foreach ($value as $arrayKey => $arrayValue) {
                $subkey = $key . '[' . self::serializePlainValue($arrayKey) . ']';
                $subitems = array_merge($subitems, self::serializePhpSerializedValue($subkey, $arrayValue, false));
            }
        } else if (is_object($value)) {
            $line .= '<' . get_class($value) . '>';
            $reflection = new \ReflectionObject($value);
            $properties = $reflection->getProperties();

            if (method_exists($value, '__sleep')) {
                $propertyNames = $value->__sleep();
            } else {
                $propertyNames = array_map(function (\ReflectionProperty $property) { return $property->getName(); }, $properties);
            }

            foreach ($propertyNames as $propertyName) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);
                $accesibilityFlag = $property->isPrivate() ? '-' : ($property->isProtected() ? '*' : '');

                $propertyValue = $property->getValue($value);
                $subkey = $key . '["' . $accesibilityFlag . $property->getName() . '"]';
                $subitems = array_merge($subitems, self::serializePhpSerializedValue($subkey, $propertyValue, false));
            }
        } else if (is_null($value)) {
            $line .= '<null>';
        }

        $output = array_merge([$line], $subitems);

        return $output;
    }

    private static function serializePlainValue($value) {
        if (is_numeric($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return '"' . str_replace('"', '\"', $value) . '"';
    }

    public function fromIniLines($key, $lines) {
        $value = substr($lines[$key], strlen($this->serializedMarker) + 1); // + space
        unset($lines[$key]);

        return self::convertValueToSerializedString($value, $lines);
    }

    private static function convertValueToSerializedString($value, $relatedKeys = []) {
        if (is_numeric($value)) {
            return (Strings::contains($value, '.') ? 'd' : 'i') . ':' . $value . ';';
        } else if (preg_match('/^<([\w\d\\\\]+)> ?(.*)/', $value, $matches)) {
            $type = $matches[1];
            $value = $matches[2];
            if ($type === 'boolean') {
                return 'b:' . ($value === 'false' ? 0 : 1) . ';';
            }

            if ($type === 'array' || class_exists($type)) {
                $items = [];
                foreach ($relatedKeys as $relatedKey => $valueOfRelatedKey) {

                    $indexAfterFirstOpeningBracket = strpos($relatedKey, '[') + 1;
                    $indexOfFirstClosingBracket = strpos($relatedKey, ']');
                    $keyLength = $indexOfFirstClosingBracket - $indexAfterFirstOpeningBracket;

                    $subkey = substr($relatedKey, $indexAfterFirstOpeningBracket, $keyLength);

                    if (class_exists($type)) {
                        if (strpos($subkey, '*') === 1) {
                            $subkey = "\"\0*\0" . substr($subkey, 2);
                        }

                        if (strpos($subkey, '-') === 1) {
                            $subkey = "\"\0{$type}\0" . substr($subkey, 2);
                        }
                    }

                    if (strpos($relatedKey, '[', $indexOfFirstClosingBracket) === false) {
                        $rel = self::findRelatedKeys($relatedKeys, $relatedKey);

                        $items[] = self::convertValueToSerializedString($subkey) . self::convertValueToSerializedString($valueOfRelatedKey, $rel);
                    }
                }

                if ($type === 'array') {
                    return 'a:' . count($items) . ':{' . join('', $items) . '}';
                }

                return 'O:' . strlen($type) . ':"' . $type . '":' . count($items) . ':{' . join('', $items) . '}';
            }

            if ($type === 'null') {
                return 'N;';
            }
        }

        if (Strings::startsWith($value, '"')) {
            $value = preg_replace('/^"(.*)"$/', '$1', $value);
        }
        return 's:' . strlen($value) . ':' . self::serializePlainValue($value) . ';';
    }

    /**
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
