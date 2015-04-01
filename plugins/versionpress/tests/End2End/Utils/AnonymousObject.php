<?php

namespace VersionPress\Tests\End2End\Utils;

class AnonymousObject {
    private $implementation = array();

    public function __construct(array $options) {
        $this->implementation = $options;
    }

    public function __call($name, $arguments) {
        if (array_key_exists($name, $this->implementation)) {
            $callable = $this->implementation[$name];

            if (is_callable($callable)) {
                return call_user_func_array($callable, $arguments);
            }
        }

        throw new \BadMethodCallException("Method {$name} does not exists");
    }

    public function __get($name) {
        return $this->implementation[$name];
    }

    public function __set($name, $value) {
        $this->implementation[$name] = $value;
    }
}