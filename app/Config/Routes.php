<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
/*Admin*/
$routes->get('/admin', 'Admin\HomeController::index');
$routes->get('/admin/login/', 'Admin\HomeController::index');
$routes->post('/home/authenticate', 'Admin\HomeController::authenticate');
$routes->get('/admin/logout', 'Admin\LogoutController::index');
$routes->get('/dashboard', 'Admin\DashboardController::index');
$routes->get('/add-user', 'Admin\AddUserController::index');
$routes->post('/adduser/insert', 'Admin\AddUserController::insert');
$routes->get('/user-masterlist', 'Admin\UserMasterlistController::index');
$routes->post('/usermasterlist/getData', 'Admin\UserMasterlistController::getData');
$routes->delete('/usermasterlist/delete/(:num)', 'Admin\UserMasterlistController::delete/$1');
$routes->get('/edit-user/(:num)', 'Admin\EditUserController::index/$1');
$routes->post('/edituser/update', 'Admin\EditUserController::update');
$routes->get('/quotation-masterlist', 'Admin\QuotationMasterlistController::index');
$routes->post('/quotationmasterlist/getData', 'Admin\QuotationMasterlistController::getData');
$routes->delete('/quotationmasterlist/delete/(:num)', 'Admin\QuotationMasterlistController::delete/$1');
$routes->post('/quotationmasterlist/updateStatus/(:num)', 'Admin\QuotationMasterlistController::updateStatus/$1');
$routes->get('/dashboard/getData', 'Admin\DashboardController::getData');
$routes->get('/send-quotation', 'Admin\SendQuotationController::index');
$routes->post('/sendquotation/insert', 'Admin\SendQuotationController::insert');
$routes->get('/request-quotation-masterlist', 'Admin\RequestQuotationListController::index');
$routes->post('/requestquotationmasterlist/getData', 'Admin\RequestQuotationListController::getData');
$routes->post('/requestquotationmasterlist/insert', 'Admin\RequestQuotationListController::insert');
$routes->post('/requestquotationmasterlist/updateStatus/(:num)', 'Admin\RequestQuotationListController::updateStatus/$1');
$routes->get('/requestquotationmasterlist/getQuotationList/(:num)', 'Admin\RequestQuotationListController::getQuotationList/$1');
$routes->get('/subscribers-masterlist', 'Admin\SubscribersMasterlistController::index');
$routes->post('/subscribersmasterlist/getData', 'Admin\SubscribersMasterlistController::getData');
$routes->delete('/subscribersmasterlist/delete/(:num)', 'Admin\SubscribersMasterlistController::delete/$1');
$routes->get('/send-newsletter', 'Admin\SendNewsletterController::index');
$routes->post('/sendnewsletter/sendMessage', 'Admin\SendNewsletterController::sendMessage');
/*Admin*/

/*User*/
$routes->post('/quotations/chargeCreditCard', 'User\QuotationsController::chargeCreditCard');
$routes->get('/quotations', 'User\QuotationsController::index');
$routes->get('/quotations/getData', 'User\QuotationsController::getData');
$routes->get('/quotations/quotationDetails', 'User\QuotationsController::quotationDetails');
$routes->post('/quotations/pay', 'User\QuotationsController::pay');
$routes->delete('quotations/delete/(:num)', 'User\QuotationsController::deleteQuotation/$1');
$routes->get('/user/login/', 'User\HomeController::index');
$routes->post('/user/authenticate', 'User\HomeController::authenticate');
$routes->get('/user/logout', 'User\LogoutController::index');
$routes->get('/request-quotation', 'User\RequestQuotationController::index');
$routes->post('requestquotation/uploadFiles', 'User\RequestQuotationController::uploadFiles');
$routes->post('requestquotation/submitQuotation', 'User\RequestQuotationController::submitQuotation');
$routes->get('/request-quotation-list', 'User\RequestQuotationListController::index');
$routes->post('/requestquotationlist/getData', 'User\RequestQuotationListController::getData');
$routes->delete('/requestquotationlist/delete/(:num)', 'User\RequestQuotationListController::delete/$1');
$routes->get('/requestquotation/quotationLists', 'User\RequestQuotationController::quotationLists');
$routes->post('requestquotation/submitQuotations', 'User\RequestQuotationController::submitQuotations');
$routes->delete('requestquotation/delete/(:num)', 'User\RequestQuotationController::delete/$1');
/*User*/

$routes->get('/', 'HomeController::index');
$routes->get('/home', 'HomeController::index');
$routes->get('/about-us', 'AboutUsController::index');
$routes->get('/contact-us', 'ContactUsController::index');
$routes->post('/contactus/sendMessage', 'ContactUsController::sendMessage');
$routes->get('/materials-and-surface-finishes', 'MaterialsAndSurfaceFinishesController::index');
$routes->get('/register', 'RegisterController::index');
$routes->post('/register/insert', 'RegisterController::insert');
$routes->post('/subscribers/insert', 'SubscribersController::insert');
$routes->get('/privacy-policy', 'PrivacyPolicyController::index');
$routes->get('/terms-and-conditions', 'TermsAndConditionsController::index');
$routes->get('/refund-and-cancellation-policy', 'RefundAndCancellationPolicyController::index');