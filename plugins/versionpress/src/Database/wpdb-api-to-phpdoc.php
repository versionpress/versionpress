<?php
// Generates wpdb API description as PhpDoc. Used by `Database`.
// Run this file as `php wpdb-api-to-phpdoc.php`.

$pathToWpdb = __DIR__ . '/../../../../ext-libs/wordpress/wp-includes/wp-db.php';

require_once $pathToWpdb;

$wpdbReflection = new ReflectionClass('wpdb');

// Generate annotations for all public properties as @property <type> <name>
$properties = $wpdbReflection->getProperties(ReflectionProperty::IS_PUBLIC);
foreach ($properties as $property) {
    preg_match_all('/@var (.*?)\n/s', $property->getDocComment(), $annotations);
    $type = @$annotations[1][0];

    echo "@property " . ($type ? $type . ' ' : '') . $property->getName() . "\n";
}

echo "\n";

// Generate annotations for all public methods as
// @method <return type> <name>(<parameters with default value if there is any>)
$methods = $wpdbReflection->getMethods(ReflectionMethod::IS_PUBLIC);
foreach ($methods as $method) {
    $parameters = $method->getParameters();
    preg_match_all('/@return (.*?)[ \n]/s', $method->getDocComment(), $annotations);

    @$returnType = $annotations[1][0];

    echo "@method " . ($returnType ? $returnType . ' ' : '') . $method->getName() . "(";

    $parametersWithDefaultValues = array_map(function (ReflectionParameter $parameter) {
        if ($parameter->isDefaultValueAvailable()) {
            if ($parameter->isDefaultValueConstant()) {
                $default = $parameter->getDefaultValueConstantName();
            } else {
                $default = str_replace(
                    ['array (', "\n"],
                    ['array(', ''],
                    var_export($parameter->getDefaultValue(), true)
                );
            }
        } else {
            $default = null;
        }

        return '$' . $parameter->getName() . ($default !== null ? ' = ' . $default : '');
    }, $method->getParameters());

    echo join(', ', $parametersWithDefaultValues);

    echo ")\n";
}
