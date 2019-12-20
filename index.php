<?php
/**
* Entry point and dispatcher
*
* This file contains script which is an entry point to web application
*/

/** loading composer default autoloader  */
require 'vendor/autoload.php';


/**
 * because of local problem with composer on my home desktop, I had to add this little workaround to load my app classes
 */
$files = glob('src/App' . '/*.php');
foreach ($files as $file) {
    require($file);
}

/** class Api used to communicate with MongoDb database, keeps connection as private property */
$Api= \App\Api::getInstance();


$Controller= new \App\Controller();

