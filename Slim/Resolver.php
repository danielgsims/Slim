<?php

namespace Slim;

use Slim\Interfaces\ResolverInterface;

class Resolver implements ResolverInterface
{
    private $container;

    public function __construct(\Pimple\Container $container)
    {
        $this->container = $container;
    }

    public function build($callable)
    {
        if (is_string($callable) && preg_match('!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!', $callable, $matches)) {

            $class = $matches[1];
            $method = $matches[2];

            $container = $this->container;

            $callable = function() use ($class, $method, $container) {
                if (isset($container[$class])) {
                    $obj = $container[$class];
                } else {
                    if (!class_exists($class)) {
                        throw new \RuntimeException('Route callable class does not exist');
                    }
                    $obj = new $class;
                }

                if (!method_exists($obj, $method)) {
                    throw new \RuntimeException('Route callable method does not exist');
                }

                return call_user_func_array(array($obj, $method), func_get_args());
            };
        }

        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($this->container);
        }

        if (!is_callable($callable)) {
            throw new \RuntimeException('Expected a callable to be added');
        }



        return $callable;
    }
}
