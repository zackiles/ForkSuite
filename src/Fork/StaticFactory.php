<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork;


//! Prefab for classes with constructors and static factory methods
abstract class StaticFactory {

    /**
     *        Return class instance
     *        @return static
     **/
    static function instance() {
        if (!ClassRegistry::exists($class=get_called_class())) {
            $ref=new \Reflectionclass($class);
            $args=func_get_args();
            ClassRegistry::set($class,
                $args?$ref->newinstanceargs($args):new $class);
        }
        return ClassRegistry::get($class);
    }

}