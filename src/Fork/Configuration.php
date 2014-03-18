<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork;

/**
 * Static class used for setting global configuration settings..
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class Configuration
{

    // ==================================================================
    //
    // Environment
    //
    // ------------------------------------------------------------------
    const forkApplicationRoot = '/var/www/forksuite/src/Fork';
    const forkDocumentRoot = '/var/www/forksuite/public';
    const forkBaseUrl = 'http://fork.local';
    // the location to store client data
    // DO NOT name the folder "mount" as it could interfere with API method routes.
    const clientMountRoot = '/var/www/forksuite/public/client-mounts';
    const mimeTypeList = '/var/www/forksuite/src/Fork/Core/File/mime.types';
    const vendorRoot = '/var/www/forksuite/vendor';
    const prongDirectory = '/var/www/forksuite/Prongs';


    // ==================================================================
    //
    // Core
    //
    // ------------------------------------------------------------------
    const version = "1.0";
    const productionMode = false;
    const productName = "ForkSuite";

    // ==================================================================
    //
    // Client
    //
    // ------------------------------------------------------------------

    // allowing guest clients turns on auto-registering for clients without
    // api keys. the clients are instead tracked by IP.
    const allowGuestClients = true;
    const allowTextPlainContent = true;

    // ==================================================================
    //
    // System
    //
    // ------------------------------------------------------------------
    const mountDirectoryMask = 0777;
    const mountFileMask = 0600;
//    const uploadMaXFileSize = 1;  // in bytes

} 