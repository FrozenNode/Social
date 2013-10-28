<?php
return array(

    // 'base_url' => URL::route(Config::get('anvard::routes.login')),

    'providers' => array (


        'Facebook' => array (
            'enabled' => true,
            'keys'    => array ( 'id' => '', 'secret' => '' ),
        ),

        'Twitter' => array (
            'enabled' => true,
            'keys'    => array ( 'key' => '', 'secret' => '' )
        ),

        'LinkedIn' => array (
            'enabled' => true,
            'keys'    => array ( 'key' => '', 'secret' => '' )
        ),
    )







);