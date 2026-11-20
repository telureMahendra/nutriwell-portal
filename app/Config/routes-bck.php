<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Admin Panel Routes
$routes->get('/', 'Admin\Index_con::index');
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

// $routes->match(['get', 'post'], 'banner/', 'Admin\Banner::index');
// $routes->match(['get', 'post'], 'banner/add', 'Admin\Banner::add');
// $routes->match(['get', 'post'], 'banner/close', 'Admin\Banner::close');
// $routes->match(['get', 'post'], 'banner/edit/(:num)', 'Admin\Banner::edit/$1');

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
// Add this line with your other coupon routes
$routes->match(['get', 'post'], 'coupon/activate', 'Admin\Coupon::activate');

$routes->match(['get', 'post'], 'doctor/', 'Admin\Doctor::index');
$routes->match(['get', 'post'], 'doctor/add', 'Admin\Doctor::add');
$routes->match(['get', 'post'], 'doctor/close', 'Admin\Doctor::close');
$routes->match(['get', 'post'], 'doctor/edit/(:num)', 'Admin\Doctor::edit/$1');

$routes->match(['get', 'post'], 'clients', 'Admin\Clients::index');
$routes->match(['get', 'post'], 'clients/', 'Admin\Clients::index');
$routes->match(['get', 'post'], 'clients/add', 'Admin\Clients::add');
$routes->match(['get', 'post'], 'clients/close', 'Admin\Clients::close');
$routes->match(['get', 'post'], 'clients/edit/(:num)', 'Admin\Clients::edit/$1');
$routes->match(['get', 'post'], 'clients/view/(:num)', 'Admin\Clients::view/$1');

$routes->match(['get', 'post'], 'clients/comment/(:num)', 'Admin\Clients::listcomment/$1');
$routes->match(['get', 'post'], 'clients/postcomment/', 'Admin\Clients::postcomment/');
$routes->match(['get', 'post'], 'clients/closecomment/', 'Admin\Clients::closecomment/');

// Calorie routes - Fix these lines
$routes->post('calorie/get-calorie-data', 'Admin\CalorieController::getCalorieData');
$routes->post('calorie/save-calorie-data', 'Admin\CalorieController::saveCalorieData');
$routes->post('calorie/delete-food-item', 'Admin\CalorieController::deleteFoodItem');
$routes->post('calorie/delete-activity-item', 'Admin\CalorieController::deleteActivityItem');

// Calories Burned Routes - ADD THESE AFTER YOUR EXISTING CLIENT ROUTES
$routes->get('clients/get-chart-data/(:num)', 'Admin\Clients::getChartData/$1');
$routes->post('clients/get-activities-by-date', 'Admin\Clients::getActivitiesByDate');
$routes->post('clients/save-calorie-burned', 'Admin\Clients::saveCalorieBurned');
$routes->get('clients/get-activity/(:num)', 'Admin\Clients::getActivity/$1');
$routes->delete('clients/delete-calorie-burned/(:num)', 'Admin\Clients::deleteCalorieBurned/$1');
$routes->post('clients/save-calorie-target', 'Admin\Clients::saveCalorieTarget');
$routes->get('clients/get-burned-stats/(:num)', 'Admin\Clients::getBurnedStats/$1');

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



//$routes->match(['get', 'post'], 'appointment/add', 'Admin\Appointment::add');
//$routes->match(['get', 'post'], 'appointment/edit/(:num)', 'Appointment::edit/$1');
//$routes->match(['get', 'post'], 'appointment/addplan/(:num)', 'Appointment::addplan/$1');
//$routes->match(['get', 'post'], 'appointment/download_workout/(:segment)', 'Doctor\Appointment::download_workout/$1');
//$routes->match(['get', 'post'], 'appointment/download_diet/(:segment)', 'Doctor\Appointment::download_diet/$1');

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

// History page
$routes->match(['get', 'post'], 'notifications/history', 'Admin\Notifications::history');

// ⭐ ADD THIS ROUTE — FIXES THE ERROR ⭐
$routes->post('notifications/store', 'Admin\Notifications::store');

$routes->post('admin/water/getWaterData', 'Admin\Water::getWaterData');
$routes->post('admin/water/saveWaterData', 'Admin\Water::saveWaterData');

//$routes->match(['get', 'post'], 'booking/add', 'Admin\Booking::add');
//$routes->match(['get', 'post'], 'booking/plan_info', 'Admin\Booking::plan_info');
//$routes->match(['get', 'post'], 'booking/apply_coupon', 'Admin\Booking::apply_coupon');
//$routes->match(['get', 'post'], 'booking/edit/(:num)', 'Admin\Booking::edit/$1');

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

// Mentor Client Management Routes
$routes->match(['get', 'post'], 'mentor/clients', 'Mentor\Client::index');
$routes->match(['get', 'post'], 'mentor/clients/view/(:num)', 'Mentor\Client::view/$1');

$routes->match(['get', 'post'], 'home', 'Home::index');
$routes->match(['get', 'post'], 'notifications', 'Notifications::index');
$routes->match(['get', 'post'], 'notifications/planexpire', 'Notifications::planexpire');
$routes->match(['get', 'post'], 'notifications/planexpired', 'Notifications::planexpired');
$routes->match(['get', 'post'], 'notifications/tomorrowappointment', 'Notifications::tomorrowappointment');

$routes->match(['get', 'post'], 'payment', 'Payment::index');
$routes->match(['get', 'post'], 'payment/success', 'Payment::Success');
$routes->match(['get', 'post'], 'payment/cancel', 'Payment::Cancel');
$routes->match(['get', 'post'], 'payment/checkorder/(:any)', 'Payment::CheckOrder/$1');

// Api Routes 
$routes->match(['get', 'post'], 'api', 'Api::getUser');
$routes->match(['get', 'post'], 'api/(:num)', 'Api::getUser/$1');
$routes->match(['get', 'post'], 'api/delete/(:num)', 'Api::delete/$1');
$routes->match(['get', 'post'], 'api/updateuser/(:num)', 'Api::updateUser/$1');
$routes->match(['get', 'post'], 'api/adduser', 'Api::addUser');
// $routes->match(['get', 'post'], 'clientlogin/send_otp', 'Clientlogin::send_otp');
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

$routes->match(['get', 'post'], 'apis/clientlogin/logout', 'Apis\Clientlogin::logout');
$routes->match(['get', 'post'], 'apis/clientlogin/delete-account', 'Apis\Clientlogin::delete_account');

$routes->match(['get', 'post'], 'apis/plans/ccavenue_decrypt', 'Apis\Plans::ccavenue_decrypt');

$routes->post('/adminweight/add', 'WeightController::addWeightEntry');
$routes->post('admin/weight/update', 'WeightController::updateWeightEntry');
$routes->get('admin/weight/get/(:num)', 'WeightController::getWeightEntry/$1');
$routes->post('admin/weight/delete', 'WeightController::deleteWeightEntry');
$routes->get('admin/weight/graph-data/(:num)/(:num)', 'WeightController::getWeightGraphData/$1/$2');

// BMI Routes
$routes->post('admin/clients/saveBmi', 'Admin\Clients::saveBmi');
$routes->get('admin/clients/getBmiHistory/(:num)', 'Admin\Clients::getBmiHistory/$1');
$routes->get('admin/clients/getBmiRecord/(:num)', 'Admin\Clients::getBmiRecord/$1');
$routes->delete('admin/clients/deleteBmi/(:num)', 'Admin\Clients::deleteBmi/$1');

$routes->group('admin', ['filter' => 'auth'], function($routes) {
    // Steps routes
    $routes->post('steps/save', 'Admin\StepsController::saveSteps');
    $routes->post('steps/target', 'Admin\StepsController::saveTarget');
    $routes->get('steps/history/(:num)', 'Admin\StepsController::getStepsHistory/$1');
    $routes->delete('steps/delete/(:num)', 'Admin\StepsController::deleteSteps/$1');
});

