<?php
return array(
    /* The name of the User model */
    'usermodel' => 'User',

    /* The name of the Profile model */
    'profilemodel' => 'Profile',

    /* The hasMany name for the profiles of a given user */
    'userprofiles' => 'profiles',

    /* The belongsTo name for the user of a given profile */
    'profileuser' => 'user',

    /* The name of the users table */
    'userstable' => 'users',

    /* The name of the profiles table */
    'profilestable' => 'profiles',

    /* The name of the foreignkey in the profiles table specifying a user */
    'profilestableforeignkey' => 'user_id',

    /* Maps Profile fields to User fields, for the case where we are creating a new User and want some internal User model values copied in */
    'profiletousermap' => array(
        'email' => 'email',
        // 'firstName' => 'forename',
        // 'lastName' => 'surname',
    ),

    /* Rules to override User saving validation (e.g. with Ardent models), for example the password.  Set to NULL to use the default validation rules. */
    'userrules' => array(
        //'password' => NULL,
    ),

    /* Specific things to set on new (unsaved) user models, provide a callable if you wish */
    'uservalues' => array(
        // 'role_id' => 3,
        // 'username' => function($user, $adapter_profile) {
        //     return $adapter_profile->email;
        // }
    ),
);
