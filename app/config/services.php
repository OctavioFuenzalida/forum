<?php

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set(
   'url',
       function () use ($config) {
           $url = new \Phalcon\Mvc\Url();
           $url->setBaseUri($config->application->baseUri);
           return $url;
       },
       true
);

/**
 * Setting up volt
 */
$di->set(
   'volt',
       function ($view, $di) {

           $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);

           $volt->setOptions(
                array(
                    "compiledPath"      => __DIR__ . "/../cache/volt/",
                    "compiledSeparator" => "_"
                )
           );

           return $volt;
       },
       true
);

/**
 * Setting up the view component
 */
$di->set(
   'view',
       function () use ($config) {

           $view = new \Phalcon\Mvc\View();

           $view->setViewsDir($config->application->viewsDir);

           $view->registerEngines(
                array(
                    ".volt" => 'volt'
                )
           );

           return $view;
       },
       true
);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set(
   'db',
       function () use ($config) {

           /*$eventsManager = new Phalcon\Events\Manager();

           $logger = new \Phalcon\Logger\Adapter\File("../app/logs/db.log");

           //Listen all the database events
           $eventsManager->attach('db', function($event, $connection) use ($logger) {
               if ($event->getType() == 'beforeQuery') {
                   $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
               }
           });*/

           $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
               "host"     => $config->database->host,
               "username" => $config->database->username,
               "password" => $config->database->password,
               "dbname"   => $config->database->name
           ));

           //Assign the eventsManager to the db adapter instance
           //$connection->setEventsManager($eventsManager);

           return $connection;
       }
);

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
if ($config->debug->enable != true) {
    $di->set(
       'modelsMetadata',
           function () use ($config) {
               return new \Phalcon\Mvc\Model\Metadata\Files(array(
                   'metaDataDir' => __DIR__ . '/../cache/metaData/'
               ));
           },
           true
    );
}

/**
 * Start the session the first time some component request the session service
 */
$di->set(
   'session',
       function () {
           $session = new \Phalcon\Session\Adapter\Files();
           $session->start();
           return $session;
       },
       true
);

/**
 * Router
 */
$di->set(
   'router',
       function () {
           return include __DIR__ . "/routes.php";
       },
       true
);

/**
 * Register the configuration itself as a service
 */
$di->set('config', $config);

/**
 * Register the flash service with the Twitter Bootstrap classes
 */
$di->set(
   'flash',
       function () {
           return new Phalcon\Flash\Direct(array(
               'error'   => 'alert alert-error',
               'success' => 'alert alert-success',
               'notice'  => 'alert alert-info',
           ));
       }
);

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set(
   'flashSession',
       function () {
           return new Phalcon\Flash\Session(array(
               'error'   => 'alert alert-error',
               'success' => 'alert alert-success',
               'notice'  => 'alert alert-info',
           ));
       }
);

$di->set(
   'dispatcher',
       function () {
           $dispatcher = new Phalcon\Mvc\Dispatcher();
           $dispatcher->setDefaultNamespace('Phosphorum\Controllers');
           return $dispatcher;
       }
);

/**
 * View cache
 */


$di->set(
   'viewCache',
       function () use ($config) {

           //Cache data for one day by default
           $frontCache = new \Phalcon\Cache\Frontend\Output(array(
               "lifetime" => ($config->debug->enable ? 0 : 2592000)
           ));

           /*return new \Phalcon\Cache\Backend\Apc($frontCache, array(
               "prefix" => "cache-"
           ));*/

           //Memcached connection settings
           return new \Phalcon\Cache\Backend\File($frontCache, array(
               "cacheDir" => __DIR__ . "/../cache/views/",
               "prefix"   => "cache-"
           ));
       }
);
