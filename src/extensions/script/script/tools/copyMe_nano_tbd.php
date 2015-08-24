<?php
/**
 * This is a template nano initiator file for you to build on. You will need to place this OUTSIDE the nano directory since it is specific to your
 * use of the library.
 */

        if( !defined( 'DIRECTORY_SEPARATOR' ) ) define( 'DIRECTORY_SEPARATOR', DIRECTORY_SEPARATOR );

        /**
         * This is the path to the nano directory (external reference to the nano subdirectory in the nscript SVN)
         */
//        define( 'ONE_SCRIPT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'nano' );

        /**
         * This is the path to your custom nano folder, typically places under a custom directory
         */
//        define( 'ONE_SCRIPT_CUSTOM_PATH', dirname(__FILE__) . '/../' . 'custom' . DIRECTORY_SEPARATOR . 'nano' );
        //define( 'ONE_SCRIPT_CUSTOM_PATH', dirname(__FILE__) . '/' . 'custom' . DIRECTORY_SEPARATOR . 'nano' );

        /**
         * This initiates the autoloader so all classes can be found
         */
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'autoload.php');
