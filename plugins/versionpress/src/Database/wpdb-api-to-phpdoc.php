<?php
// Generates wpdb API description as PhpDoc. Used by `Database`.

$r = new ReflectionClass('wpdb');
$props = $r->getProperties(ReflectionProperty::IS_PUBLIC);

foreach ($props as $prop) {
    preg_match_all('/@var (.*?)\n/s', $prop->getDocComment(), $annotations);
    $type = $annotations[1][0];

    echo "@property ", $type, " ", $prop->getName(), "\n";
}

$methods = $r->getMethods(ReflectionMethod::IS_PUBLIC);

foreach ($methods as $method) {
    $parameters = $method->getParameters();
    preg_match_all('/@return (.*?)[ \n]/s', $method->getDocComment(), $annotations);
    $returnType = $annotations[1][0];

    echo "@method ", $returnType, " ", $method->getName(), "(";

    $params = array_map(function (ReflectionParameter $parameter) {
        return '$' . $parameter->getName();
    }, $method->getParameters());

    echo join(', ', $params);

    echo ")\n";
}
