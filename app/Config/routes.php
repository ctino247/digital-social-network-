<?php

/** @var \App\Core\Router $router */

// Guest Middlewares & Auth Middlewares
$auth = [\App\Middleware\AuthMiddleware::class];
$guest = [\App\Middleware\GuestMiddleware::class];
$admin = [\App\Middleware\AdminMiddleware::class];
$creator = [\App\Middleware\CreatorMiddleware::class];

// Home & Explore Feed
$router->get('/', 'HomeController@index');
$router->get('/explore', 'ExploreController@index');

// Auth Routing
$router->get('/auth/register', 'AuthController@register', $guest);
$router->post('/auth/register', 'AuthController@handleRegister', $guest);
$router->get('/auth/login', 'AuthController@login', $guest);
$router->post('/auth/login', 'AuthController@handleLogin', $guest);
$router->get('/auth/logout', 'AuthController@logout');
$router->get('/auth/verify', 'AuthController@verifyEmail');
$router->get('/auth/forgot-password', 'AuthController@forgotPassword', $guest);
$router->post('/auth/forgot-password', 'AuthController@handleForgotPassword', $guest);
$router->get('/auth/reset-password', 'AuthController@resetPassword', $guest);
$router->post('/auth/reset-password', 'AuthController@handleResetPassword', $guest);

// Profile Routing
$router->get('/profile/{username}', 'ProfileController@index');
$router->get('/profile/{username}/followers', 'ProfileController@followers');
$router->get('/profile/{username}/following', 'ProfileController@following');
$router->post('/profile/update', 'ProfileController@update', $auth);
$router->post('/profile/{id}/follow', 'ProfileController@follow', $auth);
$router->post('/profile/apply-creator', 'ProfileController@applyCreator', $auth);
$router->post('/profile/withdraw', 'ProfileController@requestWithdrawal', $auth);
$router->get('/wallet', 'ProfileController@wallet', $auth);

// Social Routing
$router->get('/post/create', 'PostController@create', $auth);
$router->post('/post/create', 'PostController@store', $auth);
$router->get('/post/{id}', 'PostController@show');
$router->post('/post/{id}/reply', 'PostController@reply', $auth);
$router->post('/post/{id}/quote', 'PostController@quote', $auth);
$router->post('/post/{id}/like', 'PostController@like', $auth);
$router->post('/post/{id}/bookmark', 'PostController@bookmark', $auth);
$router->post('/poll/vote', 'PostController@votePoll', $auth);

// Marketplace Routing
$router->get('/marketplace', 'MarketplaceController@index');
$router->get('/product/{slug}', 'MarketplaceController@detail');
$router->get('/product/{id}/download', 'MarketplaceController@download', $auth);
$router->get('/recommendation/{code}/analytics', 'MarketplaceController@recommendationAnalytics', $auth);

// Cart & Checkout Routing
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/buy-now', 'CartController@buyNow');
$router->post('/cart/remove', 'CartController@remove');
$router->post('/cart/coupon', 'CartController@applyCoupon');
$router->post('/cart/checkout', 'CartController@checkout', $auth);

// Flutterwave Checkout & Webhook Routing
$router->get('/flutterwave/simulate-checkout', 'FlutterwaveController@simulateCheckout');
$router->post('/flutterwave/process-simulation', 'FlutterwaveController@processSimulation');
$router->post('/flutterwave/webhook', 'FlutterwaveController@webhook');

// Message Routing (Direct Chats)
$router->get('/messages', 'MessageController@index', $auth);
$router->get('/messages/new', 'MessageController@new', $auth);
$router->get('/messages/{username}', 'MessageController@chat', $auth);
$router->post('/messages/send', 'MessageController@send', $auth);
$router->get('/api/messages/{username}/updates', 'MessageController@getUpdates', $auth);

// Notifications Routing
$router->get('/notifications', 'NotificationController@index', $auth);
$router->post('/notifications/read-all', 'NotificationController@readAll', $auth);

// Creator Program Routing
$router->get('/creator/dashboard', 'CreatorController@dashboard', $creator);
$router->get('/creator/products/new', 'CreatorController@newProduct', $creator);
$router->post('/creator/products/new', 'CreatorController@storeProduct', $creator);
$router->get('/creator/products/{id}/edit', 'CreatorController@editProduct', $creator);
$router->post('/creator/products/{id}/edit', 'CreatorController@updateProduct', $creator);
$router->get('/creator/coupons', 'CreatorController@coupons', $creator);
$router->post('/creator/coupons/new', 'CreatorController@newCoupon', $creator);

// Admin Control Panel Routing
$router->get('/admin/dashboard', 'AdminController@dashboard', $admin);
$router->get('/admin/users', 'AdminController@users', $admin);
$router->post('/admin/users/{id}/verify', 'AdminController@verifyUser', $admin);
$router->get('/admin/creator-applications', 'AdminController@creatorApplications', $admin);
$router->post('/admin/creator-applications/{id}/approve', 'AdminController@approveCreator', $admin);
$router->post('/admin/creator-applications/{id}/reject', 'AdminController@rejectCreator', $admin);
$router->get('/admin/products', 'AdminController@products', $admin);
$router->post('/admin/products/{id}/approve', 'AdminController@approveProduct', $admin);
$router->post('/admin/products/{id}/reject', 'AdminController@rejectProduct', $admin);
$router->get('/admin/orders', 'AdminController@orders', $admin);
$router->get('/admin/settings', 'AdminController@settings', $admin);
$router->post('/admin/settings', 'AdminController@updateSettings', $admin);
$router->get('/admin/withdrawals', 'AdminController@withdrawals', $admin);
$router->post('/admin/withdrawals/{id}/approve', 'AdminController@approveWithdrawal', $admin);
$router->post('/admin/withdrawals/{id}/reject', 'AdminController@rejectWithdrawal', $admin);
