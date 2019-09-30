<?php

use App\Role;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdminRole = new Role();
        $superAdminRole->name = "super-admin";
        $superAdminRole->display_name = "Super Administrator";
        $superAdminRole->description = "Super Administrator";
        $superAdminRole->save();

        $adminRole = new Role();
        $adminRole->name = "admin";
        $adminRole->display_name = "Administrator";
        $adminRole->description = "Administrator";
        $adminRole->save();

        $editorRole = new Role();
        $editorRole->name = "editor";
        $editorRole->display_name = "Editor";
        $editorRole->description = "Editor";
        $editorRole->save();

        $viewerRole = new Role();
        $viewerRole->name = "viewer";
        $viewerRole->display_name = "Viewer";
        $viewerRole->description = "Viewer";
        $viewerRole->save();

        $mobileUserRole = new Role();
        $mobileUserRole->name = "verified_mobile_user";
        $mobileUserRole->display_name = "Verified Mobile User";
        $mobileUserRole->description = "Verified Mobile User";
        $mobileUserRole->save();

        $verifiedMobileUserRole = new Role();
        $verifiedMobileUserRole->name = "mobile_user";
        $verifiedMobileUserRole->display_name = "Mobile User";
        $verifiedMobileUserRole->description = "Mobile User";
        $verifiedMobileUserRole->save();

        // USERS PERMISSION
        $createUserPermission = new \App\Permission();
        $createUserPermission->name         = 'create-user';
        $createUserPermission->display_name = 'can create new users';
        $createUserPermission->description  = 'can create new users';
        $createUserPermission->save();

        $updateUserPermission = new \App\Permission();
        $updateUserPermission->name         = 'update-user';
        $updateUserPermission->display_name = 'can update users';
        $updateUserPermission->description  = 'can update users';
        $updateUserPermission->save();

        $deleteUserPermission = new \App\Permission();
        $deleteUserPermission->name         = 'delete-user';
        $deleteUserPermission->display_name = 'can delete users';
        $deleteUserPermission->description  = 'can delete users';
        $deleteUserPermission->save();

        $viewUserPermission = new \App\Permission();
        $viewUserPermission->name         = 'view-users';
        $viewUserPermission->display_name = 'can view UsersController';
        $viewUserPermission->description  = 'can view users';
        $viewUserPermission->save();

        // NEWS FEED PERMISSION
        $createNewsFeedPermission = new \App\Permission();
        $createNewsFeedPermission->name         = 'create-news-feed';
        $createNewsFeedPermission->display_name = 'can create new news_feeds';
        $createNewsFeedPermission->description  = 'can create new news_feeds';
        $createNewsFeedPermission->save();

        $updateNewsFeedPermission = new \App\Permission();
        $updateNewsFeedPermission->name         = 'update-news-feed';
        $updateNewsFeedPermission->display_name = 'can update news_feeds';
        $updateNewsFeedPermission->description  = 'can update news_feeds';
        $updateNewsFeedPermission->save();

        $deleteNewsFeedPermission = new \App\Permission();
        $deleteNewsFeedPermission->name         = 'delete-news-feed';
        $deleteNewsFeedPermission->display_name = 'can delete NewsFeeds';
        $deleteNewsFeedPermission->description  = 'can delete NewsFeeds';
        $deleteNewsFeedPermission->save();

        $viewNewsFeedPermission = new \App\Permission();
        $viewNewsFeedPermission->name         = 'view-news-feed';
        $viewNewsFeedPermission->display_name = 'can view NewsFeeds';
        $viewNewsFeedPermission->description  = 'can view NewsFeeds';
        $viewNewsFeedPermission->save();


        // MOBILE USERS PERMISSION
        $normal_mobile_user = new \App\Permission();
        $normal_mobile_user->name         = 'normal-mobile-user';
        $normal_mobile_user->display_name = 'Is Normal Mobile User';
        $normal_mobile_user->description  = 'Is Normal Mobile User';
        $normal_mobile_user->save();

        $verified_mobile_user = new \App\Permission();
        $verified_mobile_user->name         = 'verified-mobile-user';
        $verified_mobile_user->display_name = 'Is Verified Mobile Viewer';
        $verified_mobile_user->description  = 'Is Verified Mobile Viewer';
        $verified_mobile_user->save();

        $createTeamsPermission = new \App\Permission();
        $createTeamsPermission->name         = 'create-teams';
        $createTeamsPermission->display_name = 'can Create Teams';
        $createTeamsPermission->description  = 'can Create Teams';
        $createTeamsPermission->save();

        $superAdminRole->attachPermissions(
            array(
                $createUserPermission,
                $updateUserPermission,
                $deleteUserPermission,
                $viewUserPermission,

                $createNewsFeedPermission,
                $updateNewsFeedPermission,
                $deleteNewsFeedPermission,
                $viewNewsFeedPermission,
                $createTeamsPermission,
            ));

        $adminRole->attachPermissions(
            array(
                $createUserPermission,
                $updateUserPermission,
                $deleteUserPermission,
                $viewUserPermission,

                $createNewsFeedPermission,
                $updateNewsFeedPermission,
                $deleteNewsFeedPermission,
                $viewNewsFeedPermission,
                $createTeamsPermission,


            ));

        $editorRole->attachPermissions(
            array(
                $viewUserPermission,

                $updateNewsFeedPermission,
                $viewNewsFeedPermission,
            ));

        $viewerRole->attachPermissions(
            array(
                $viewUserPermission,

                $viewNewsFeedPermission,
            ));

        $mobileUserRole->attachPermissions(
            array(
                $normal_mobile_user,
            ));

        $verifiedMobileUserRole->attachPermissions(
            array(
                $normal_mobile_user,
                $verified_mobile_user,
            ));

    }
}
