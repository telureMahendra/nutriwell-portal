<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// $routes->get('chat/getClientsByIds', 'ChatController::getClientsByIds');
$routes->get('chat/getClientsByIds', 'ChatController::getClientsByIds');
$routes->post('chat/uploadFile', 'Apis\ChatFileController::upload');
$routes->get('chat/file/serve/(:num)', 'Apis\ChatFileController::serve/$1');

// API routes for chat
$routes->get('apis/chat/getClientsByIds', 'ChatController::getClientsByIds');
$routes->post('apis/chat/uploadFile', 'Apis\ChatFileController::upload');
$routes->get('apis/chat/file/serve/(:num)', 'Apis\ChatFileController::serve/$1');

// ---- Portal chat module (floating widget) ----
// Admin — can chat with any client
$routes->get('admin/chat/contacts',        'Admin\Chat::contacts');
$routes->post('admin/chat/log',            'Admin\Chat::log');
$routes->get('admin/chat/history',         'Admin\Chat::history');
$routes->get('admin/chat/firebase-token',  'Admin\Chat::firebaseToken');
// Mentor — only associated clients
$routes->get('mentor/chat/contacts',       'Mentor\Chat::contacts');
$routes->post('mentor/chat/log',           'Mentor\Chat::log');
$routes->get('mentor/chat/history',        'Mentor\Chat::history');
$routes->get('mentor/chat/firebase-token', 'Mentor\Chat::firebaseToken');
$routes->post('api/health/save', 'Apis\HealthTrackerController::saveHealthData');


// Admin Panel Routes
$routes->get('/', 'Admin\Index_con::index');
$routes->get('delete-account', 'Home::deleteAccount');
$routes->get('invite', 'Home::invite');
$routes->add('login_con/check_login', 'Admin\Login_con::check_login', ['as' => 'login']);
$routes->add('index_con', 'Admin\Index_con::index');

$routes->match(['get', 'post'], 'users', 'Admin\Users::index');
// For Sales Agents
$routes->get('users/sales-agents', 'Admin\Users::salesAgents');
$routes->match(['get', 'post'], 'users/activate', 'Admin\Users::activate');

$routes->match(['get', 'post'], 'users/add', 'Admin\Users::add');
$routes->match(['get', 'post'], 'users/view/(:num)', 'Admin\Users::view/$1');
$routes->match(['get', 'post'], 'users/edit/(:num)', 'Admin\Users::edit/$1');
$routes->match(['get', 'post'], 'users/close', 'Admin\Users::close');
$routes->match(['get', 'post'], 'users/role/(:num)', 'Admin\Users::role/$1');

$routes->match(['get', 'post'], 'medical_condition/', 'Admin\Medical_condition::index');
$routes->match(['get', 'post'], 'medical_condition/add', 'Admin\Medical_condition::add');
$routes->match(['get', 'post'], 'medical_condition/close', 'Admin\Medical_condition::close');
$routes->match(['get', 'post'], 'medical_condition/edit/(:num)', 'Admin\Medical_condition::edit/$1');

$routes->match(['get', 'post'], 'language/', 'Admin\Language::index');
$routes->match(['get', 'post'], 'language/add', 'Admin\Language::add');
$routes->match(['get', 'post'], 'language/close', 'Admin\Language::close');
$routes->match(['get', 'post'], 'language/edit/(:num)', 'Admin\Language::edit/$1');

$routes->match(['get', 'post'], 'faq/', 'Admin\Faq::index');
$routes->match(['get', 'post'], 'faq/add', 'Admin\Faq::add');
$routes->match(['get', 'post'], 'faq/close', 'Admin\Faq::close');
$routes->match(['get', 'post'], 'faq/edit/(:num)', 'Admin\Faq::edit/$1');

$routes->match(['get', 'post'], 'testimonial/', 'Admin\Testimonial::index');
$routes->match(['get', 'post'], 'testimonial/add', 'Admin\Testimonial::add');
$routes->match(['get', 'post'], 'testimonial/close', 'Admin\Testimonial::close');
$routes->match(['get', 'post'], 'testimonial/delete', 'Admin\Testimonial::delete');
$routes->match(['get', 'post'], 'testimonial/edit/(:num)', 'Admin\Testimonial::edit/$1');
$routes->match(['get', 'post'], 'testimonial/update_approval', 'Admin\Testimonial::update_approval');
$routes->match(['get', 'post'], 'testimonial/view/(:num)', 'Admin\Testimonial::view/$1');

$routes->match(['get', 'post'], 'city/', 'Admin\City::index');
$routes->match(['get', 'post'], 'city/add', 'Admin\City::add');
$routes->match(['get', 'post'], 'city/close', 'Admin\City::close');
$routes->match(['get', 'post'], 'city/edit/(:num)', 'Admin\City::edit/$1');

$routes->match(['get', 'post'], 'goal/', 'Admin\Goal::index');
$routes->match(['get', 'post'], 'goal/add', 'Admin\Goal::add');
$routes->match(['get', 'post'], 'goal/close', 'Admin\Goal::close');
$routes->match(['get', 'post'], 'goal/edit/(:num)', 'Admin\Goal::edit/$1');

$routes->match(['get', 'post'], 'lifestyle/', 'Admin\Lifestyle::index');
$routes->match(['get', 'post'], 'lifestyle/add', 'Admin\Lifestyle::add');
$routes->match(['get', 'post'], 'lifestyle/close', 'Admin\Lifestyle::close');
$routes->match(['get', 'post'], 'lifestyle/edit/(:num)', 'Admin\Lifestyle::edit/$1');

$routes->match(['get', 'post'], 'food/', 'Admin\Food::index');
$routes->match(['get', 'post'], 'food/add', 'Admin\Food::add');
$routes->match(['get', 'post'], 'food/close', 'Admin\Food::close');
$routes->match(['get', 'post'], 'food/edit/(:num)', 'Admin\Food::edit/$1');
$routes->match(['get', 'post'], 'food/importfood', 'Admin\Food::importfood');

$routes->match(['get', 'post'], 'food/list_type', 'Admin\Food::list_type');
$routes->match(['get', 'post'], 'food/add_type', 'Admin\Food::add_type');
$routes->match(['get', 'post'], 'food/close_type', 'Admin\Food::close_type');
$routes->match(['get', 'post'], 'food/edit_type/(:num)', 'Admin\Food::edit_type/$1');

$routes->match(['get', 'post'], 'coupon/', 'Admin\Coupon::index');
$routes->match(['get', 'post'], 'coupon/add', 'Admin\Coupon::add');
$routes->match(['get', 'post'], 'coupon/close', 'Admin\Coupon::close');
$routes->match(['get', 'post'], 'coupon/edit/(:num)', 'Admin\Coupon::edit/$1');
$routes->match(['get', 'post'], 'coupon/usage/(:num)', 'Admin\Coupon::usage/$1');
$routes->match(['get', 'post'], 'coupon/activate', 'Admin\Coupon::activate');
$routes->get('coupon/clientSearch', 'Admin\Coupon::clientSearch');

$routes->match(['get', 'post'], 'doctor/', 'Admin\Doctor::index');

$routes->match(['get', 'post'], 'banners/', 'Admin\Banners::index');
$routes->match(['get', 'post'], 'banners/add', 'Admin\Banners::add');
$routes->match(['get', 'post'], 'banners/close', 'Admin\Banners::close');
$routes->match(['get', 'post'], 'banners/edit/(:num)', 'Admin\Banners::edit/$1');
$routes->match(['get', 'post'], 'doctor/add', 'Admin\Doctor::add');
$routes->match(['get', 'post'], 'doctor/close', 'Admin\Doctor::close');
$routes->match(['get', 'post'], 'doctor/edit/(:num)', 'Admin\Doctor::edit/$1');

$routes->match(['get', 'post'], 'clients', 'Admin\Clients::index');
$routes->match(['get', 'post'], 'clients/', 'Admin\Clients::index');
$routes->match(['get', 'post'], 'clients/add', 'Admin\Clients::add');
$routes->match(['get', 'post'], 'clients/close', 'Admin\Clients::close');
$routes->match(['get', 'post'], 'clients/edit/(:num)', 'Admin\Clients::edit/$1');
$routes->match(['get', 'post'], 'clients/view/(:num)', 'Admin\Clients::view/$1');
$routes->post('clients/set_targets/(:num)', 'Admin\Clients::set_targets/$1');

$routes->match(['get', 'post'], 'clients/comment/(:num)', 'Admin\Clients::listcomment/$1');
$routes->match(['get', 'post'], 'clients/postcomment/', 'Admin\Clients::postcomment/');
$routes->match(['get', 'post'], 'clients/closecomment/', 'Admin\Clients::closecomment/');
$routes->match(['get', 'post'], 'clients/add_subscription', 'Admin\Clients::add_subscription');
$routes->match(['get', 'post'], 'clients/add_subscription/(:num)', 'Admin\Clients::add_subscription/$1');

// =============================================
// FIXED CALORIE ROUTES - USE THESE
// =============================================
$routes->post('calorie/getCalorieData', 'Admin\CalorieController::getCalorieData');
$routes->post('calorie/saveCalorieData', 'Admin\CalorieController::saveCalorieData');
$routes->post('calorie/deleteFoodItem', 'Admin\CalorieController::deleteFoodItem');
$routes->post('calorie/deleteActivityItem', 'Admin\CalorieController::deleteActivityItem');
$routes->post('calorie/updateTargets', 'Admin\CalorieController::updateTargets');
$routes->get('calorie/getFoodList', 'Admin\CalorieController::getFoodList');
// Additional calorie routes
$routes->post('calorie/getFoodTypes', 'Admin\CalorieController::getFoodTypes');
$routes->post('calorie/quickAdd', 'Admin\CalorieController::quickAddCalorie');


// Calories Burned Routes (if needed separately)
$routes->get('clients/get-chart-data/(:num)', 'Admin\Clients::getChartData/$1');
$routes->post('clients/get-activities-by-date', 'Admin\Clients::getActivitiesByDate');
$routes->post('clients/save-calorie-burned', 'Admin\Clients::saveCalorieBurned');
$routes->get('clients/get-activity/(:num)', 'Admin\Clients::getActivity/$1');
$routes->delete('clients/delete-calorie-burned/(:num)', 'Admin\Clients::deleteCalorieBurned/$1');
$routes->post('clients/save-calorie-target', 'Admin\Clients::saveCalorieTarget');
$routes->get('clients/get-burned-stats/(:num)', 'Admin\Clients::getBurnedStats/$1');


//weight tracker
$routes->post('weight/add', 'Admin\WeightController::addWeightEntry');
$routes->post('weight/update', 'Admin\WeightController::updateWeightEntry');
$routes->get('weight/get/(:num)', 'Admin\WeightController::getWeightEntry/$1');
$routes->post('weight/delete', 'Admin\WeightController::deleteWeightEntry');
// $routes->get('weight/graph-data/(:num)/(:num)', 'Admin\WeightController::getWeightGraphData/$1/$2');

$routes->match(['get', 'post'], 'weight/graph-data/(:num)/(:num)', 'Admin\WeightController::getWeightGraphData/$1/$2');

$routes->get('weight/get-data/(:num)', 'Admin\WeightController::getWeightData/$1');




$routes->match(['get', 'post'], 'plan/', 'Admin\Plan::index');
$routes->match(['get', 'post'], 'plan/add', 'Admin\Plan::add');
$routes->match(['get', 'post'], 'plan/close', 'Admin\Plan::close');
$routes->match(['get', 'post'], 'plan/edit/(:num)', 'Admin\Plan::edit/$1');
$routes->match(['get', 'post'], 'plan/add_new_row', 'Admin\Plan::add_new_row');
$routes->match(['get', 'post'], 'plan/view/(:num)', 'Admin\Plan::view/$1');

$routes->match(['get', 'post'], 'appointment/', 'Admin\Appointment::index');
$routes->match(['get', 'post'], 'appointment/close', 'Admin\Appointment::close');
$routes->match(['get', 'post'], 'appointment/clientdashboard/(:num)', 'Admin\Appointment::clientdashboard/$1');

$routes->match(['get', 'post'], 'appointment/comment/(:num)', 'Admin\Appointment::listcomment/$1');
$routes->match(['get', 'post'], 'appointment/postcomment/', 'Admin\Appointment::postcomment/');
$routes->match(['get', 'post'], 'appointment/closecomment/', 'Admin\Appointment::closecomment/');
$routes->post('appointment/set_client_targets/(:num)', 'Admin\Appointment::set_client_targets/$1');

// $routes->match(['get', 'post'], 'appointment/add', 'Admin\Appointment::add');
// $routes->match(['get', 'post'], 'appointment/edit/(:num)', 'Appointment::edit/$1');
// $routes->match(['get', 'post'], 'appointment/addplan/(:num)', 'Appointment::addplan/$1');
// $routes->match(['get', 'post'], 'appointment/download_workout/(:segment)', 'Doctor\Appointment::download_workout/$1');
// $routes->match(['get', 'post'], 'appointment/download_diet/(:segment)', 'Doctor\Appointment::download_diet/$1');

$routes->match(['get', 'post'], 'booking/', 'Admin\Booking::index');
$routes->match(['get', 'post'], 'booking/close', 'Admin\Booking::close');
$routes->match(['get', 'post'], 'booking/view/(:num)', 'Admin\Booking::view/$1');
$routes->match(['get', 'post'], 'booking/assign_doctor', 'Admin\Booking::assign_doctor');
$routes->match(['get', 'post'], 'booking/viewpdf/(:num)', 'Admin\Booking::viewpdf/$1');

// Push Notification Module
$routes->match(['get', 'post'], 'notifications/', 'Admin\Notifications::index');
$routes->match(['get', 'post'], 'notifications/create', 'Admin\Notifications::create');
$routes->match(['get', 'post'], 'notifications/view/(:num)', 'Admin\Notifications::view/$1');
$routes->match(['get', 'post'], 'notifications/edit/(:num)', 'Admin\Notifications::edit/$1');
//$routes->match(['get', 'post'], 'notifications/delete/(:num)', 'Admin\Notifications::delete/$1');
$routes->get('notifications/delete/(:num)', 'Admin\Notifications::delete/$1');

// Optional: send manually
$routes->match(['get', 'post'], 'notifications/send/(:num)', 'Admin\Notifications::send/$1');
$routes->get('notifications/searchClients', 'Admin\Notifications::searchClients');

// History page
$routes->match(['get', 'post'], 'notifications/history', 'Admin\Notifications::history');

// ⭐ ADD THIS ROUTE — FIXES THE ERROR ⭐
$routes->post('notifications/store', 'Admin\Notifications::store');





$routes->post('admin/water/getWaterData', 'Admin\Water::getWaterData');
$routes->post('admin/water/saveWaterData', 'Admin\Water::saveWaterData');
$routes->post('admin/water/getWaterRangeData', 'Admin\Water::getWaterRangeData');



//$routes->match(['get', 'post'], 'booking/add', 'Admin\Booking::add');
$routes->match(['get', 'post'], 'clients/plan_info', 'Admin\Clients::plan_info');
$routes->match(['get', 'post'], 'clients/apply_coupon', 'Admin\Clients::apply_coupon');
//$routes->match(['get', 'post'], 'booking/edit/(:num)', 'Admin\Booking::edit/$1');

// Monitoring Route — Admin
$routes->get('monitoring',  'Admin\Monitoring::index');
$routes->get('monitoring/', 'Admin\Monitoring::index');

// Challenge Routes — Admin
$routes->match(['get', 'post'], 'challenges',               'Admin\Challenges::index');
$routes->match(['get', 'post'], 'challenges/',              'Admin\Challenges::index');
$routes->match(['get', 'post'], 'challenges/create',        'Admin\Challenges::create');
$routes->match(['get', 'post'], 'challenges/store',         'Admin\Challenges::store');
$routes->match(['get', 'post'], 'challenges/edit/(:num)',   'Admin\Challenges::edit/$1');
$routes->match(['get', 'post'], 'challenges/update/(:num)', 'Admin\Challenges::update/$1');
$routes->get('challenges/view/(:num)',                       'Admin\Challenges::view/$1');
$routes->get('challenges/delete/(:num)',                     'Admin\Challenges::delete/$1');
$routes->post('challenges/toggle_status',                   'Admin\Challenges::toggle_status');

$routes->match(['get', 'post'], 'logout/', 'Admin\Logout::index');

// Mentor Panel Routes
$routes->get('/mentor', 'Mentor\Index_con::index');
$routes->match(['get', 'post'], 'mentor', 'Mentor\Index_con::index');
$routes->match(['get', 'post'], 'mentor/check_login', 'Mentor\Index_con::check_login');
$routes->match(['get', 'post'], 'mentor/appointment/', 'Mentor\Appointment::index');
$routes->match(['get', 'post'], 'mentor/appointment/view/(:num)', 'Mentor\Appointment::view/$1');
$routes->match(['get', 'post'], 'mentor/appointment/complete/', 'Mentor\Appointment::complete');
$routes->match(['get', 'post'], 'mentor/appointment/addplan/(:num)', 'Mentor\Appointment::addplan/$1');
$routes->match(['get', 'post'], 'mentor/appointment/download_workout/(:segment)', 'Mentor\Appointment::download_workout/$1');
$routes->match(['get', 'post'], 'mentor/appointment/download_diet/(:segment)', 'Mentor\Appointment::download_diet/$1');
$routes->match(['get', 'post'], 'mentor/appointment/download_prescription/(:segment)', 'Mentor\Appointment::download_prescription/$1');
$routes->match(['get', 'post'], 'mentor/logout/', 'Mentor\Logout::index');

$routes->match(['get', 'post'], 'mentor/appointment/comment/(:num)', 'Mentor\Appointment::listcomment/$1');
$routes->match(['get', 'post'], 'mentor/appointment/postcomment/', 'Mentor\Appointment::postcomment/');
$routes->match(['get', 'post'], 'mentor/appointment/closecomment/', 'Mentor\Appointment::closecomment/');
$routes->match(['get', 'post'], 'mentor/appointment/clientdashboard/(:num)', 'Mentor\Appointment::clientdashboard/$1');
$routes->post('mentor/appointment/set_client_targets/(:num)', 'Mentor\Appointment::set_client_targets/$1');

// Mentor Client Management Routes
$routes->match(['get', 'post'], 'mentor/clients', 'Mentor\Client::index');
$routes->match(['get', 'post'], 'mentor/clients/view/(:num)', 'Mentor\Client::view/$1');
$routes->post('mentor/clients/set_targets/(:num)', 'Mentor\Client::set_targets/$1');

// Challenge Routes — Mentor
$routes->match(['get', 'post'], 'mentor/challenges',               'Mentor\Challenges::index');
$routes->match(['get', 'post'], 'mentor/challenges/',              'Mentor\Challenges::index');
$routes->match(['get', 'post'], 'mentor/challenges/create',        'Mentor\Challenges::create');
$routes->match(['get', 'post'], 'mentor/challenges/store',         'Mentor\Challenges::store');
$routes->match(['get', 'post'], 'mentor/challenges/edit/(:num)',   'Mentor\Challenges::edit/$1');
$routes->match(['get', 'post'], 'mentor/challenges/update/(:num)', 'Mentor\Challenges::update/$1');
$routes->get('mentor/challenges/view/(:num)',                       'Mentor\Challenges::view/$1');
$routes->get('mentor/challenges/delete/(:num)',                     'Mentor\Challenges::delete/$1');

$routes->match(['get', 'post'], 'home', 'Home::index');
$routes->match(['get', 'post'], 'notifications', 'Notifications::index');
$routes->match(['get', 'post'], 'notifications/planexpire', 'Notifications::planexpire');
$routes->match(['get', 'post'], 'notifications/planexpired', 'Notifications::planexpired');
$routes->match(['get', 'post'], 'notifications/tomorrowappointment', 'Notifications::tomorrowappointment');

$routes->match(['get', 'post'], 'payment', 'Payment::index');
$routes->match(['get', 'post'], 'payment/success', 'Payment::Success');
$routes->match(['get', 'post'], 'payment/cancel', 'Payment::Cancel');
$routes->match(['get', 'post'], 'payment/checkorder/(:any)', 'Payment::CheckOrder/$1');

// API Routes 
$routes->match(['get', 'post'], 'api', 'Api::getUser');
$routes->match(['get', 'post'], 'api/(:num)', 'Api::getUser/$1');
$routes->match(['get', 'post'], 'api/delete/(:num)', 'Api::delete/$1');
$routes->match(['get', 'post'], 'api/updateuser/(:num)', 'Api::updateUser/$1');
$routes->match(['get', 'post'], 'api/adduser', 'Api::addUser');
$routes->match(['get', 'post'], 'apis/clientlogin/sendotp', 'Apis\Clientlogin::sendOtp');
$routes->match(['get', 'post'], 'apis/clientlogin/verify_otp', 'Apis\Clientlogin::verify_otp');
$routes->match(['get', 'post'], 'apis/clientlogin/resend', 'Apis\Clientlogin::resend');
$routes->match(['get', 'post'], 'apis/profile_info/profile', 'Apis\Profile_info::profile');
$routes->match(['get', 'post'], 'apis/profile_info/addage', 'Apis\Profile_info::addage');
$routes->match(['get', 'post'], 'apis/profile_info/addheight', 'Apis\Profile_info::addheight');
$routes->match(['get', 'post'], 'apis/profile_info/addweight', 'Apis\Profile_info::addweight');
$routes->match(['get', 'post'], 'apis/profile_info/adddiet', 'Apis\Profile_info::adddiet');
$routes->match(['get', 'post'], 'apis/profile_info/view_medical_condition', 'Apis\Profile_info::view_medical_condition');
$routes->match(['get', 'post'], 'apis/profile_info/medical_condition', 'Apis\Profile_info::medical_condition');
$routes->match(['get', 'post'], 'apis/profile_info/view_lifestyle', 'Apis\Profile_info::view_lifestyle');
$routes->match(['get', 'post'], 'apis/profile_info/life_style', 'Apis\Profile_info::life_style');
$routes->match(['get', 'post'], 'apis/profile_info/view_goals', 'Apis\Profile_info::view_goals');
$routes->match(['get', 'post'], 'apis/profile_info/add_goal', 'Apis\Profile_info::add_goal');
$routes->match(['get', 'post'], 'apis/profile_info/view_profile', 'Apis\Profile_info::view_profile');
$routes->match(['get', 'post'], 'apis/profile_info/update_profile', 'Apis\Profile_info::update_profile');
$routes->match(['get', 'post'], 'apis/profile_info/notifications', 'Apis\Profile_info::notifications');
$routes->match(['get', 'post'], 'apis/plans/plans_list', 'Apis\Plans::plans_list');
$routes->match(['get', 'post'], 'apis/plans/view_plans', 'Apis\Plans::view_plans');
$routes->match(['get', 'post'], 'apis/plans/plan_booking', 'Apis\Plans::plan_booking');
$routes->match(['get', 'post'], 'apis/plans/booking_payment', 'Apis\Plans::booking_payment');
$routes->match(['get', 'post'], 'apis/plans/booking_details', 'Apis\Plans::booking_details');
$routes->match(['get', 'post'], 'apis/plans/subscription', 'Apis\Plans::subscription');
$routes->match(['get', 'post'], 'apis/plans/payment_details', 'Apis\Plans::payment_details');
$routes->match(['get', 'post'], 'apis/testimonial/testimonial_list', 'Apis\Testimonial::testimonial_list');
$routes->match(['get', 'post'], 'apis/testimonial/addtestimonial', 'Apis\Testimonial::addTestimonial');
$routes->match(['get', 'post'], 'apis/testimonial/image_testimonials', 'Apis\Testimonial::image_testimonials');
$routes->match(['get', 'post'], 'apis/testimonial/text_testimonials', 'Apis\Testimonial::text_testimonials');
$routes->match(['get', 'post'], 'apis/testimonial/submit_testimonial', 'Apis\Testimonial::submit_testimonial');
$routes->match(['get', 'post'], 'apis/firm/contact_details', 'Apis\Firm::contact_details');
$routes->match(['get', 'post'], 'apis/faq/faq_list', 'Apis\Faq::faq_list');
$routes->match(['get', 'post'], 'apis/appointment/view_appointment', 'Apis\Appointment::view_appointment');
$routes->match(['get', 'post'], 'apis/appointment/book_appointment', 'Apis\Appointment::book_appointment');
$routes->match(['get', 'post'], 'apis/appointment/book_appointment_list', 'Apis\Appointment::book_appointment_list');
$routes->match(['get', 'post'], 'apis/appointment/appointment_details', 'Apis\Appointment::appointment_details');
$routes->match(['get', 'post'], 'apis/appointment/close_appointment', 'Apis\Appointment::close_appointment');
$routes->match(['get', 'post'], 'apis/appointment/slot_appointment', 'Apis\Appointment::slot_appointment');
$routes->match(['get', 'post'], 'apis/profile_info/update_profile_image', 'Apis\Profile_info::update_profile_image');
$routes->match(['get', 'post'], 'apis/profile_info/notification', 'Apis\Profile_info::notification');
$routes->match(['get', 'post'], 'apis/profile_info/coupens_list', 'Apis\Profile_info::coupens_list');
$routes->match(['get', 'post'], 'apis/profile_info/apply_coupon', 'Apis\Profile_info::apply_coupon');
$routes->match(['get', 'post'], 'apis/profile_info/appointment_doctor_details', 'Apis\Profile_info::appointment_doctor_details');
$routes->match(['get', 'post'], 'apis/profile_info/book_appointment', 'Apis\Profile_info::book_appointment');
$routes->match(['get', 'post'], 'apis/profile_info/appointment_details', 'Apis\Profile_info::appointment_details');
$routes->match(['get', 'post'], 'apis/profile_info/update_appointment', 'Apis\Profile_info::update_appointment');
$routes->match(['get', 'post'], 'apis/profile_info/cancel_appointment', 'Apis\Profile_info::cancel_appointment');
$routes->match(['get', 'post'], 'apis/water/get_water', 'Apis\Water::get_water');
$routes->match(['get', 'post'], 'apis/water/update_water', 'Apis\Water::update_water');
$routes->match(['get', 'post'], 'apis/water/get_week_water', 'Apis\Water::get_week_water');
$routes->match(['get', 'post'], 'apis/calorie/get_calorie', 'Apis\Calorie::get_calorie');
$routes->match(['get', 'post'], 'apis/calorie/food_type', 'Apis\Calorie::food_type');
$routes->match(['get', 'post'], 'apis/calorie/food_list', 'Apis\Calorie::food_list');
$routes->match(['get', 'post'], 'apis/calorie/add_food', 'Apis\Calorie::add_food');
$routes->match(['get', 'post'], 'apis/calorie/remove_food', 'Apis\Calorie::remove_food');
$routes->match(['get', 'post'], 'apis/weight/get_weight', 'Apis\Weight::get_weight');
$routes->match(['get', 'post'], 'apis/weight/update_weight', 'Apis\Weight::update_weight');
$routes->match(['get', 'post'], 'apis/weight/get_week_weight', 'Apis\Weight::get_week_weight');
$routes->match(['get', 'post'], 'apis/banners/homepage_banner', 'Apis\Banners::homepage_banner');
$routes->match(['get', 'post'], 'apis/profile_info/steps_calories', 'Apis\Profile_info::steps_calories');
$routes->match(['get', 'post'], 'apis/profile_info/last_steps_calories', 'Apis\Profile_info::last_steps_calories');
$routes->match(['get', 'post'], 'apis/profile_info/add_body_measurements', 'Apis\Profile_info::add_body_measurements');
$routes->match(['get', 'post'], 'apis/profile_info/get_body_measurements', 'Apis\Profile_info::get_body_measurements');


$routes->match(['get', 'post'], 'apis/clientlogin/logout', 'Apis\Clientlogin::logout');
$routes->match(['get', 'post'], 'apis/clientlogin/delete-account', 'Apis\Clientlogin::delete_account');

$routes->match(['get', 'post'], 'apis/plans/ccavenue_decrypt', 'Apis\Plans::ccavenue_decrypt');

$routes->match(['get', 'post'], 'apis/dashboard/update_dashboard_summary', 'Apis\Dashboard::save_daily_summary');
$routes->match(['get', 'post'], 'apis/dashboard/get_dashboard_summary', 'Apis\Dashboard::get_daily_summary');
$routes->post('apis/plans/claim_free_trial', 'Apis\Plans::claim_free_trial');

// Challenge Routes — Flutter API
$routes->match(['get', 'post'], 'apis/challenges/list',           'Apis\Challenges::list');
$routes->match(['get', 'post'], 'apis/challenges/join',           'Apis\Challenges::join');
$routes->match(['get', 'post'], 'apis/challenges/my_challenges',  'Apis\Challenges::my_challenges');
$routes->match(['get', 'post'], 'apis/challenges/daily_checkin',  'Apis\Challenges::daily_checkin');
$routes->match(['get', 'post'], 'apis/challenges/detail',         'Apis\Challenges::detail');
$routes->match(['get', 'post'], 'apis/challenges/user_stats',     'Apis\Challenges::user_stats');

// Rewards Routes - Flutter API
$routes->match(['get', 'post'], 'apis/rewards/summary',           'Apis\Rewards::summary');
$routes->match(['get', 'post'], 'apis/rewards/history',           'Apis\Rewards::history');
$routes->match(['get', 'post'], 'apis/rewards/redeem',            'Apis\Rewards::redeem');

// Payment Gateway Routes - Flutter API
$routes->match(['get', 'post'], 'apis/payment/razorpay_credentials', 'Apis\Payment::razorpay_credentials');



// BMI Routes - Using dedicated BmiController
$routes->post('bmi/saveBmi', 'Admin\BmiController::saveBmi');
$routes->get('bmi/getBmiHistory/(:num)', 'Admin\BmiController::getBmiHistory/$1');
$routes->get('bmi/getBmiRecord/(:num)', 'Admin\BmiController::getBmiRecord/$1');
$routes->delete('bmi/deleteBmi/(:num)', 'Admin\BmiController::deleteBmi/$1');
$routes->get('bmi/getBmiChartData/(:num)', 'Admin\BmiController::getBmiChartData/$1');
$routes->get('bmi/getBmiStats/(:num)', 'Admin\BmiController::getBmiStats/$1');



// Steps Routes - Following the same pattern as BMI routes
$routes->post('steps/saveSteps', 'Admin\StepsController::saveSteps');
$routes->get('steps/getStepsHistory/(:num)', 'Admin\StepsController::getStepsHistory/$1');
$routes->get('steps/getStepsData/(:num)', 'Admin\StepsController::getStepsData/$1');
$routes->delete('steps/deleteSteps/(:num)', 'Admin\StepsController::deleteSteps/$1');
$routes->get('steps/getStepsChartData/(:num)', 'Admin\StepsController::getStepsChartData/$1');
$routes->get('steps/getStepsStats/(:num)', 'Admin\StepsController::getStepsStats/$1');
$routes->post('steps/saveTarget', 'Admin\StepsController::saveTarget');
$routes->get('steps/getDateData/(:num)', 'Admin\StepsController::getDateData/$1');
$routes->post('steps/quickUpdate', 'Admin\StepsController::quickUpdate');


// Calories Burned Routes
$routes->post('calories/saveCalories', 'Admin\CaloriesBurnedController::saveCalories');
$routes->get('calories/getCaloriesHistory/(:num)', 'Admin\CaloriesBurnedController::getCaloriesHistory/$1');
$routes->get('calories/getCaloriesData/(:num)', 'Admin\CaloriesBurnedController::getCaloriesData/$1');
$routes->delete('calories/deleteCalories/(:num)', 'Admin\CaloriesBurnedController::deleteCalories/$1');
$routes->get('calories/getCaloriesChartData/(:num)', 'Admin\CaloriesBurnedController::getCaloriesChartData/$1');
$routes->get('calories/getCaloriesStats/(:num)', 'Admin\CaloriesBurnedController::getCaloriesStats/$1');
$routes->post('calories/saveTarget', 'Admin\CaloriesBurnedController::saveTarget');
$routes->get('calories/getDateData/(:num)', 'Admin\CaloriesBurnedController::getDateData/$1');
$routes->post('calories/quickUpdate', 'Admin\CaloriesBurnedController::quickUpdate');


$routes->get('calories/getCaloriesComparison/(:num)', 'Admin\CaloriesBurnedController::getCaloriesComparison/$1');
$routes->post('calories/deleteCaloriesByDate', 'Admin\CaloriesBurnedController::deleteCaloriesByDate');
$routes->get('calories/getActivityTypes', 'Admin\CaloriesBurnedController::getActivityTypes');