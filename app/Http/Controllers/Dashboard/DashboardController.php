<?php

namespace App\Http\Controllers\Dashboard;

use App\Member;
use App\NewsFeed;
use App\NewsFeedComment;
use App\NewsFeedLike;
use App\Partnership;
use App\PrayerRequest;
use App\Team;
use App\TeamMember;
use App\User;
use App\User_log;
use App\UserUsageLog;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{

    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
    }

    public function getDailyMobileUserChartData($date_ref) {
        try{
            $cur_time = Carbon::now();
            $cur_time->subDay($date_ref);
            $mobile_User_data = array();
            $mobile_User_data['date'] = $cur_time->toDateString();
            $mobile_User_data['mobile_user_count'] = 0;
            $mobile_User_registered = User::where('user_type_id', 2 )->whereDate('created_at', '=', $cur_time->toDateString())->count();
            $mobile_User_logged_in = User_log::whereDate('last_login_at', '=', $cur_time->toDateString())->count();
            $mobile_User_data['mobile_user_registered_today'] = $mobile_User_registered ;
            $mobile_User_data['mobile_user_logged_in_today'] = $mobile_User_logged_in ;
            return response()->json(['status'=>true, 'mobile_user_chart_data'=> $mobile_User_data],200);
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'error'=> $exception->getMessage()],500);
        }
    }
    public function getWeeklyMobileUserChartData($date_ref){
        try{
            $cur_time = Carbon::now();
            $cur_time->subWeek(  $date_ref);
            $mobile_User_data = array();
            $mobile_User_data['date'] = $cur_time->toDateString();
            $mobile_User_data['weekly_created_users'] = User::where('user_type_id', 2 )->whereBetween('created_at', [$cur_time->copy()->startOfWeek(), $cur_time->copy()->endOfWeek()])->count();
            $mobile_User_data['weekly_logged_in__users'] = User_log::whereBetween('last_login_at', [$cur_time->copy()->startOfWeek(), $cur_time->copy()->endOfWeek()])->count();
//            $messageData['total_received'] = ReceivedMessage::whereIn('sent_from', Student::select('phone')->get())->whereBetween('created_at', [$cur_time->copy()->startOfWeek(), $cur_time->copy()->endOfWeek()])->count();
//            $messageData['gender_proportion'] = $this->getWeeklyGenderChartData($date_ref);
//            $messageData['location_proportion'] = $this->getWeeklyLocationsChartData($date_ref);
//            $messageData['student_year_proportion'] = $this->getWeeklyStudentYearChartData($date_ref);
            for($i = 0; $i <= 6; $i++) {
                $cur_time = $cur_time->copy()->startOfWeek();
                $sent_message_count = User::whereBetween('created_at', [$cur_time->copy()->startOfWeek(), $cur_time->copy()->endOfWeek()])->count();
//                $received_message_count = ReceivedMessage::whereBetween('created_at', [$cur_time->copy()->startOfWeek(), $cur_time->copy()->endOfWeek()])->count();
                $mobile_User_data['mobile_User_data_weekly'][] = $sent_message_count;
//                $messageData['received_messages'][] = $received_message_count;
//                $messageData['labels'][] = $cur_time->format('d/M/Y');
                $cur_time->subWeek(1);
            }
            return response()->json(['status'=>true, 'messages_chart_data'=> $mobile_User_data],200);
        }catch (\Exception $exception) {
            return response()->json(['status'=>false, 'error'=> $exception->getMessage()],500);
        }
    }

    public function getMainDashboardData() {
        try{
            $mainDashboard = array();
            $mainDashboard['all_members'] = Member::where('status', '=', true)->count();
            $mainDashboard['all_team'] = Team::where('status', '=', true)->count();
            $mainDashboard['all_team_members'] =TeamMember::distinct('member_id')->pluck('member_id')->count();
            $mainDashboard['all_system_users'] = User::count();
            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'main_dashboard'=>$mainDashboard], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feed_comments'], 500);
        }
    }
    public function getNewsFeedDashboardData() {
        try{
            $feedsDashboard = array();
            $feedsDashboard['published_feeds'] = NewsFeed::where('is_published', '=', true)->count();
            $feedsDashboard['not_published_feeds'] = NewsFeed::where('is_published', '=', false)->count();
            $feedsDashboard['feeds_likes'] = NewsFeedLike::count();
            $feedsDashboard['feeds_comments'] = NewsFeedComment::count();
            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'feeds_dashboard'=>$feedsDashboard], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feed_comments'], 500);
        }
    }
    public function getPrayerRequestDashboardData() {
        try{
            $prayerRequestDashboard = array();
            $prayerRequestDashboard['prayer_requests'] = PrayerRequest::count();
            $prayerRequestDashboard['prayer_requests_replied'] = PrayerRequest::where('is_replied', '=', true)->count();
            $prayerRequestDashboard['prayer_requests_not_replied'] = PrayerRequest::where('is_replied', '=', false)->count();
            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'prayer_dashboard'=>$prayerRequestDashboard], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feed_comments'], 500);
        }
    }

    public function getMemberDashboardData() {
        try{
            $memberDashboard = array();
            $memberDashboard['all_members'] = Member::where('status', '=', true)->count();
            $memberDashboard['male_members'] = Member::where('status', '=', true)
                ->where('gender', 'LIKE', '%male')
                ->orWhere('gender', 'LIKE', 'ወንድ')
                ->count();
            $memberDashboard['female_members'] = Member::where('status', '=', true)
                ->where('gender', 'LIKE', '%female')
                ->orWhere('gender', 'LIKE', 'ሴት')
                ->count();
            $memberDashboard['previous_members'] = Member::where('status', '=', false)->count();

            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'member_dashboard'=>$memberDashboard], 200);
        } catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find dashboard cata'], 500);
        }
    }

    public function getTeamsDashboardData() {
        try{
            $credential = request()->only('team_id');

            $rules = [
                'team_id' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $team = Team::where('id', '=', $credential['team_id'])->get()->first();
            if(! $team instanceof Team){
                return response()->json(['error'=> 'Team not found'],404);
            }

            $team_id = $team->id;
            $memberDashboard = array();
            $memberDashboard['team_members'] = TeamMember::where('team_id', '=', $team_id)
                ->where('status', '=', true)->count();

            $memberDashboard['team_leaders'] = TeamMember::where('team_id', '=', $team_id)
                ->where('status', '=', true)
                ->where('is_leader', '=', true)
                ->where(function($q) use ($team_id) {
                    $q->where(function($query) use ($team_id){
                        $query->where("is_main_leader", "=", false);
                    })
                        ->orWhere(function($query) use ($team_id) {
                            $query->where("is_main_leader", "=", true);
                        })
                    ;
                })
                ->count();

            $memberDashboard['team_main_leaders'] = TeamMember::where('team_id', '=', $team->id)
                ->where('status', '=', true)
                ->where('is_leader', '=', true)
                ->where('is_main_leader', '=', true)
                ->count();

            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'member_dashboard'=>$memberDashboard], 200);
        } catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find dashboard cata'], 500);
        }
    }

    public function getPartnerDashboardData() {
        try{
            $partnerDashboard = array();
            $partnerDashboard['all_partners'] = Partnership::count();
            $partnerDashboard['partner_requests_approved'] = Partnership::where('status', '=', true)->count();
            $partnerDashboard['partner_requests_not_approved'] = Partnership::where('status', '=', false)->count();
            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'partner_dashboard'=>$partnerDashboard], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feed_comments'], 500);
        }
    }
    public function getUsersDashboardData() {
        try{
            $userDashboard = array();
            $userDashboard['all_users'] = User::count();
            $userDashboard['approved_users'] = User::where('status', '=', true)->count();
            $userDashboard['not_approved_users'] = Member::where('status', '=', false)->count();
            $userDashboard['Mobile_users'] = Member::where('user_type_id', '=', 2)->count();
            $userDashboard['system_users'] = Member::where('user_type_id', '=', 1)->count();
            return response()->json(['status'=> true,'message'=> 'Dashboard data fetched successfully', 'user_dashboard'=>$userDashboard], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feed_comments'], 500);
        }
    }
    public function getUsersDailyUsageData($date_ref){
        try{
            $cur_time = Carbon::now();
            $cur_time->subDay($date_ref);
            $cur_time->subDay(21);
            $messageData = array();
            $messageData['start_date'] = $cur_time->format('M/d/Y');
            $messageData['users_count'] = array();
            $messageData['usage_date'] = array();
            for($i = 21; $i >= 0; $i--) {
                $users_count = UserUsageLog::whereDate('log_time', '=', $cur_time->toDateString())->select('user_id')->distinct()->count('user_id');
                $messageData['users_count'][] = $users_count;
                $messageData['usage_date'][] = $cur_time->format('M d');
                $cur_time->addDay(1);
            }
            return response()->json(['status'=>true, 'users_usage_data'=> $messageData],200);
        }catch (\Exception $exception) {
            return response()->json(['status'=>false, 'error'=> $exception->getMessage()],500);
        }
    }



    public function getMembersUsageData($date_ref){
        try{
            $cur_time = Carbon::now();
            $cur_time->subDay($date_ref);
            $cur_time->subDay(21);
            $messageData = array();
            $messageData['start_date'] = $cur_time->format('M/d/Y');
            $messageData['users_count'] = array();
            $messageData['member_date'] = array();
            for($i = 21; $i >= 0; $i--) {
                $users_count = Member::whereDate('created_at', '=', $cur_time->toDateString())->select('user_id')->get()->count();
                $messageData['users_count'][] = $users_count;
                $messageData['member_date'][] = $cur_time->format('M d');
                $cur_time->addDay(1);
            }
            return response()->json(['status'=>true, 'users_member_data'=> $messageData],200);
        }catch (\Exception $exception) {
            return response()->json(['status'=>false, 'error'=> $exception->getMessage()],500);
        }
    }
}
