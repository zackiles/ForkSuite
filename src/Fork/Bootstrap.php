<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */
require_once('Configuration.php');
use Fork\Configuration;




//require_once Configuration::vendorRoot . '/Hashids/Hashids.php';
//require_once Configuration::vendorRoot . '/DotsUnited/Cabinet/Cabinet.php';




// api init
return call_user_func(function () {
    require_once Configuration::vendorRoot . '/Luracast/Restler/AutoLoader.php';
    $loader = Luracast\Restler\AutoLoader::instance();
    // Get the parent directory of the Fork source files.
    // We'll use this path as the one to pass to the include_paths for
    // proper namespace resolution.
    $forkParentDirectory = dirname(realpath(Configuration::forkApplicationRoot)) . '/';
    $sailParentDirectory = dirname(realpath(Configuration::vendorRoot . '/Sail')) . '/';
    $hashIdParentDirectory = dirname(realpath(Configuration::vendorRoot . '/Hashids')) . '/';
    // Load the fork libraries
    $loader::addPath($forkParentDirectory);
    $loader::addPath($sailParentDirectory);
    $loader::addPath($hashIdParentDirectory);
    //Register auto loader for Restler.
    spl_autoload_register($loader);
    return $loader;
});
