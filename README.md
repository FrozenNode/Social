A HybridAuth Package for Laravel
=========================================

## Requirements

* Laravel 4
* Database setup for migrations
* A "User" model (you can set a different name in configuration)
* You have read the notes on configuration before you dive in :)


## Installation

1. Add the dependency to your composer.json file: `"frozennode/hybridauth": "*"`
2. Run `php composer.phar install`
3. Add `'Frozennode\HybridAuth\HybridAuthServiceProvider',` to your `config/app.php` file
3. Publish the package config `php artisan config:publish frozennode/hybridauth`
4. Add your service credentials to `app/config/packages/frozennode/hybridauth/hybridauth.php`
5. Check the `app/config/packages/frozennode/hybridauth/db.php` file to see if you need to customise anything (see [Configuration](#configuration) below for help)
6. Run the migration `php artisan migrate --package='frozennode/hybridauth'`
7. Create the `Profile` model (using a different name if you changed the config)
8. Set the User to have many Profiles:

    ```
        public function profiles() {
            return $this->hasMany('Profile');
        }
    ```

9. Set the Profile to belong to a User:

    ```
        public function user() {
            return $this->belongsTo('User');
        }
    ```

## Configuration

This package comes with several configuration files, and you *must* edit at least one of them.

Before you can run Anvard, you need to publish out the packages config files to your own app - which you can easily do using artisan:

```php
artisan config:publish atticmedia/anvard
```

You will then find the configuration files in your app/config/packages directory.


### hybridauth.php

Firstly to put in all your service credentials (app id and app secret that you got from Facebook, LinkedIn, etc).

This is _almost_ the exact set of configuration used by the HybridAuth library itself, so see their documentation for more information.

The one exception is the "base_url", which Anvard itself specifies based on the Routes it is configured to use.

### db.php

This file specifies everything related to your database and models.  Anvard expects you to have a users table, and it will create a profiles table for you with the migration.

Note that you should edit this file *BEFORE YOU RUN THE MIGRATION*, since the migration itself reads this config.

Hopefully most of the values in this file are self explanatory, the exceptions being the "profiletousermap", "userrules" and "uservalues" keys.

> #### profilestousersmap
>
This is used when a new user is created from a social login.  It is a reasonably common case that you will have some properties on User that are mapped to Profiles, but you may want to keep them directly in the User model to make your life easier when > dealing with users who were not registered using this method.
>
Specifying this will map fields from the Profile to the newly > created User.
>
Keys are attribute names from the Profile (which mirrors the original HybridAuth Adapter) and values are attribute names on the User model.
>
> #### userrules
>
> If you are using [Ardent](https://github.com/laravelbook/ardent) to automatically validate models (and stop them from being saved if validation fails) you may want to specify a set of override rules here when creating new Users.
>
> For example, we don't have passwords for users created by Anvard, so you could disable the confirmation requirement there.
>
> #### uservalues
>
> This is similar to the profiletousermap, but much more flexible.  You can specify specific values for certain attributes on new Users (i.e. a "role_id" for all "customers"), or even provide a callback to run - the callback will be passed in the new (unsaved) user and the original HybridAuth Adapter profile of values.

### views.php

Anvard provides it's own extremely simple views.  Change this setting to specify your own views instead.

## Routes

Anvard consumes the following routes:

* anvard
* anvard/login{provider}
* anvard/endpoint
