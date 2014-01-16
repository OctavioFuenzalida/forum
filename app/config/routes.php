<?php

$router = new Phalcon\Mvc\Router(false);

$router->setDefaultNamespace('Phosphorum\Controllers');

$router->add('/sitemap', array(
	'controller' => 'sitemap',
	'action' => 'index'
));

$router->add('/help', array(
	'controller' => 'discussions',
	'action' => 'help'
));

$router->add('/search', array(
	'controller' => 'discussions',
	'action' => 'search'
));

$router->add('/settings', array(
	'controller' => 'users',
	'action' => 'settings'
));

$router->add('/login/oauth/authorize', array(
	'controller' => 'users',
	'action' => 'authorize'
));

$router->add('/login/oauth/access_token/', array(
	'controller' => 'users',
	'action' => 'accessToken'
));

$router->add('/logout', array(
	'controller' => 'users',
	'action' => 'logout'
));

$router->add('/category/{id:[0-9]+}/{slug}/{offset:[0-9]+}', array(
	'controller' => 'discussions',
	'action' => 'category'
));

$router->add('/activity', array(
	'controller' => 'discussions',
	'action' => 'activity'
));

$router->add('/post/discussion', array(
	'controller' => 'discussions',
	'action' => 'create'
));

$router->add('/edit/discussion/{id:[0-9]+}', array(
	'controller' => 'discussions',
	'action' => 'edit'
));

$router->add('/user/{id:[0-9]+}/{login}', array(
	'controller' => 'users',
	'action' => 'profile'
));

$router->add('/category/{id:[0-9]+}/{slug}', array(
	'controller' => 'discussions',
	'action' => 'category'
));

$router->add('/reply/{id:[0-9]+}', array(
	'controller' => 'replies',
	'action' => 'get'
));

$router->add('/reply/update', array(
	'controller' => 'replies',
	'action' => 'update'
));

$router->add('/reply/delete/{id:[0-9]+}', array(
	'controller' => 'replies',
	'action' => 'delete'
));

$router->add('/discussions/{order:[a-z]+}', array(
	'controller' => 'discussions',
	'action' => 'index'
));

$router->add('/discussions/{order:[a-z]+}/{offset:[0-9]+}', array(
	'controller' => 'discussions',
	'action' => 'index'
));

$router->add('/discussion/{id:[0-9]+}/{slug}', array(
	'controller' => 'discussions',
	'action' => 'view'
))->setName('page-discussion');

$router->add('/', array(
	'controller' => 'discussions',
	'action' => 'index'
));

return $router;
