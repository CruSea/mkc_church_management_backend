<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ussd_test', 'UssdController@handleTest');
Route::post('/ussd_test', 'UssdController@handleTest');

Route::post('/link_test', 'UssdController@getData');

Route::group(['namespace' => 'Authenticate'], function () {
    Route::post('/authenticate', 'Authenticate@authenticate');
    Route::post('/register', 'Authenticate@register');

    Route::post('/mobile_authenticate', 'MobAuthenticate@authenticate');
    Route::post('/mobile_register', 'MobAuthenticate@register');
    Route::post('/mobile_auth_register', 'MobAuthenticate@register');
});

Route::group(['namespace' => 'Google'], function () {
    Route::get('/google_callback', 'GoogleCallback@googleCallback');
});

Route::group(['namespace' => 'Dashboard'], function () {
    Route::get('/main_dashboard', 'DashboardController@getMainDashboardData');
    Route::get('/feeds_dashboard', 'DashboardController@getNewsFeedDashboardData');
    Route::get('/prayer_request_dashboard', 'DashboardController@getPrayerRequestDashboardData');
    Route::get('/partnership_dashboard', 'DashboardController@getPartnerDashboardData');
    Route::get('/member_dashboard', 'DashboardController@getMemberDashboardData');
    Route::get('/team_dashboard', 'DashboardController@getTeamsDashboardData');

    Route::get('/mobile_user_dashboard/{id}', 'DashboardController@getDailyMobileUserChartData');
    Route::get('/dashboard_users_usage/{id}', 'DashboardController@getUsersDailyUsageData');
    Route::get('/daily_mobile_user_chart/{id}', 'DashboardController@getUsersDailyUsageData');
    Route::get('/daily_member_usage_chart/{id}', 'DashboardController@getMembersUsageData');
    Route::get('/weekly_mobile_user_chart/{id}', 'DashboardController@getWeeklyMobileUserChartData');
    Route::get('/monthly_mobile_user_chart/{id}', 'DashboardController@getMonthlyMessagesCount');
});

Route::group(['namespace' => 'NewsFeeds'], function () {
    Route::get('/news_feeds', 'NewsFeedController@getPublishedNewsFeedForMobile');
    Route::get('/news_feeds_all', 'NewsFeedController@getAllNewsFeed');
    Route::get('/news_feeds_published', 'NewsFeedController@getPublishedNewsFeed');
    Route::get('/news_feeds_not_published', 'NewsFeedController@getNotPublishedNewsFeed');
    Route::post('/news_feed', 'NewsFeedController@create');
    Route::patch('/news_feed', 'NewsFeedController@update');
    Route::put('/news_feed', 'NewsFeedController@update');
    Route::delete('/news_feed/{id}', 'NewsFeedController@delete');
});

Route::group(['namespace' => 'NewsFeeds'], function () {
    Route::get('/news_feed_comments', 'NewsFeedCommentsController@getAll');
    Route::get('/news_feed_comments/{id}', 'NewsFeedCommentsController@getCommentsList');
    Route::post('/news_feed_comment', 'NewsFeedCommentsController@create');
    Route::patch('/news_feed_comment', 'NewsFeedCommentsController@update');
    Route::put('/news_feed_comments', 'NewsFeedCommentsController@update');
    Route::delete('/news_feed_comment/{id}', 'NewsFeedCommentsController@delete');
});

Route::group(['namespace' => 'NewsFeeds'], function () {
    Route::post('/news_feed_like', 'NewsFeedLikesController@like');
    Route::post('/news_feed_unlike', 'NewsFeedLikesController@unLike');
});

Route::get('/post_type', 'PostTypeController@getPostType');

Route::group(['namespace' => 'Users'], function () {
    Route::get('/me', 'UsersController@getMe');
    Route::get('/users', 'UsersController@getUsers');
    Route::get('/public_users', 'UsersController@getMobileUsers');
    Route::post('/user_status', 'UsersController@updateUserStatus');
    Route::post('/user', 'UsersController@create');
    Route::put('/user', 'UsersController@update');
    Route::put('/public_user', 'UsersController@updateMobile');
    Route::patch('/user', 'UsersController@update');
    Route::delete('/user/{id}', 'UsersController@delete');
    Route::get('/user_roles', 'UsersController@getUserRoles');
});

Route::group(['namespace' => 'PlaylistManager'], function () {
    Route::get('/playlist', 'PlaylistManagerController@getPaginated');
    Route::post('/playlist', 'PlaylistManagerController@create');
    Route::get('/playlist/all', 'PlaylistManagerController@getAll');
    Route::patch('/playlist', 'PlaylistManagerController@update');
    Route::put('/playlist', 'PlaylistManagerController@update');
    Route::delete('/playlist/{id}', 'PlaylistManagerController@delete');
});

Route::group(['namespace' => 'Members'], function () {
    Route::get('/members', 'MemberController@getMembers');
    Route::get('/search_members', 'MemberController@getPaginatedSearch');
    Route::get('/search_members_detail', 'MemberController@getPaginatedSearchDetail');
    Route::get('/search_members_not_in_team', 'MemberController@getPaginatedSearchNotInTeam');
    Route::get('/mobile_member', 'MemberController@getSingleMobileMember');
    Route::get('/membership_requests', 'MemberController@getMemberRequests');
    Route::get('/partners', 'MemberController@getPartners');
    Route::post('/member', 'MemberController@create');
    Route::post('/new_member', 'MemberController@addMembearAdmin');
    Route::post('/member_update', 'MemberController@update');
    Route::get('/members/not_in_team', 'MemberController@getMembersNotInTeam');
    Route::get('/member/{id}', 'MemberController@show');
    Route::delete('/member/{id}', 'MemberController@delete');
    Route::post('/member_spouse', 'MemberSpouseInfoControler@create');
    Route::post('/member_spouse_admin', 'MemberSpouseInfoControler@createForAdmin');
    Route::put('/member_spouse_info', 'MemberSpouseInfoControler@update');
    Route::post('/member_spouse_info/update', 'MemberSpouseInfoControler@update');
    Route::post('/member_children', 'MemberChildrenController@create');
    Route::post('/member_children_mass', 'MemberChildrenController@createMass');
    Route::put('/member_children_info', 'MemberChildrenController@update');
    Route::delete('/member_child/{id}', 'MemberChildrenController@delete');
    Route::post('/member_previous_church', 'MemberPreviousChurchController@create');
    Route::post('/member_previous_church_admin', 'MemberPreviousChurchController@createAdmin');
    Route::put('/member_previous_church_update', 'MemberPreviousChurchController@update');
    Route::post('/member_previous_church_update_2', 'MemberPreviousChurchController@update');
    Route::post('/member_previous_church_mass', 'MemberPreviousChurchController@createMass');
    Route::get('/export', 'MemberController@exportMember');
    Route::post('/import', 'MemberController@importMember');
    Route::get('/pdf', 'MemberController@pdf');

});


Route::group(['namespace' => 'Partnership'], function () {
    Route::get('/partners', 'PartnershipController@getPartners');
    Route::get('/partnership_requests', 'PartnershipController@getPartnerShipRequests');
    Route::get('/partner_mobile', 'PartnershipController@getSinglePartner');
    Route::post('/partnership', 'PartnershipController@create');
    Route::post('/partner_admin', 'PartnershipController@addPartnerAdmin');
    Route::delete('/partner/{id}', 'PartnershipController@delete');
    Route::put('/approve_partnership', 'PartnershipController@update');
});

Route::group(['namespace' => 'PrayerRequest'], function () {
    Route::get('/prayer_requests', 'PrayerRequestController@getPrayerRequests');
    Route::get('/prayer_data_mobile', 'PrayerRequestController@getSinglePrayerRequestData');
    Route::post('/prayer_request', 'PrayerRequestController@create');
    Route::post('/prayer_request_status', 'PrayerRequestController@updatePrayerStatus');
    Route::delete('/prayer_request/{id}', 'PrayerRequestController@delete');
    Route::get('/prayer_replied', 'PrayerRequestController@getReply');
    Route::post('/prayer_reply', '/PrayerRequestController@reply');
    Route::put('/prayer_reply', 'PrayerRequestController@reply');
});

Route::group(['namespace' => 'Donation'], function () {
    Route::get('/donation_reports', 'DonationReportsController@getDonationReports');
    Route::delete('/donation_report/{id}', 'DonationReportsController@delete');
    Route::post('/donation_report', 'DonationReportsController@create');

});


Route::group(['namespace' => 'Teams'], function () {
    Route::get('/teams', 'TeamsController@getPaginated');
    Route::post('/team', 'TeamsController@create');
    Route::delete('/team/{id}', 'TeamsController@delete');
    Route::get('/team/{id}', 'TeamsController@show');
    Route::post('/team/update', 'TeamsController@update');
    Route::get('/team/search', 'TeamsController@getPaginatedSearch');

    //team categories
    Route::get('/team_categories', 'TeamsController@getCategories');
    Route::post('/team_category', 'TeamsController@createCategory');
    Route::post('/team_category/update', 'TeamsController@updateCategory');
    Route::get('/team_categories/paginated', 'TeamsController@getCategoriesPaginated');
    Route::delete('/team_category/{id}', 'TeamsController@deleteCategory');

    //team Members
    Route::get('/team_members', 'TeamMembersController@getTeamMembers');
    Route::post('/team_member', 'TeamMembersController@create');
    Route::delete('/team_member/{id}', 'TeamMembersController@delete');
    Route::post('/team_member/update', 'TeamMembersController@update');
    Route::get('/team_member/search', 'TeamMembersController@getPaginatedSearch');
    Route::get('/team_member/leaders', 'TeamMembersController@getTeamLeaders');
    Route::get('/team_member/main_leaders', 'TeamMembersController@getTeamMainLeaders');
    Route::get('/team_member/{id}', 'TeamMembersController@show');
    Route::get('/team_member/leaders/all', 'TeamMembersController@getTeamLeadersAll');
    Route::get('/team_member/main_leaders/all', 'TeamMembersController@getTeamMainLeadersAll');
});



