<?php
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

/** @var \Zend\Expressive\Application $app */

//system api path
if ($container->has('api-datastore')) {
    $app->route('/api/datastore[/{resourceName}[/{id}]]', 'api-datastore', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], 'api-datastore');
}
if ($container->has('webhookActionRender')) {
    $app->route('/webhook[/{resourceName}]', 'webhookActionRender', ['GET', 'POST'], 'webhook');
}

if ($container->has('analyse-result-page')) {
    $app->route('/analyseResult', 'analyse-result-page', ['GET'], 'analyse-result-page');
}

if ($container->has('analyse-search-result-page')) {
    $app->route('/analyseSearchResult', 'analyse-search-result-page', ['GET'], 'analyse-search-result-page');
}

if ($container->has('analyse-view-page')) {
    $app->route('/analyseView', 'analyse-view-page', ['GET'], 'analyse-view-page');
}

if ($container->has('home-page')) {
    $app->route('/', 'home-page', ['GET'], 'home-page');
}
