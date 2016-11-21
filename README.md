# zappem-laravel
Connect your Laravel application to Zappem

## Installation

    $ composer require zappem/zappem-laravel

Then add the following line to your `ServiceProviders` array in `config/app.php`:
    
    Zappem\ZappemLaravel\ServiceProvider::class

## Configuration

Now you'll need to configure Zappem. You can define your configuration in your env file.

The env variables are:

    ZAPPEM_URL=http://localhost:3000
    ZAPPEM_PROJECT=123456
    ZAPPEM_ENABLE=true

- ZAPPEM_URL - The full URL (including port number) where Zappem is running.
- ZAPPEM_PROJECT - The project ID for this application. You can find this on Zappem.
- ZAPPEM_ENABLE - true/false

Alternatively you can define these values in a configuration file.
    
    $ php artisan vendor:publish

The configuration file will be located in `/config/zappem.php`.

Note: You may need to rebuild the cache when changing config variables in Laravel

    $ php artisan config:cache

## Usage

Your application should report errors to Zappem in the `report()` function in `app/Exceptions/Handler.php`.

Here's an example of how it should look:

    public function report(Exception $e){
        if(Config::get('services.zappem.zappem_enable')) \Zappem::exception($e)->send();
    }

### Passing through a user

If your application requires users to log in, you can send the currently logged in user to Zappem. This will then display in Zappem when viewing the exception.

    public function report(Exception $e){
        if(Config::get('services.zappem.zappem_enable')) \Zappem::exception($e)->user(Auth::user()->id)->send();
    }

The `user()` function currently accepts a string or an integer. You should pass through a unique idenfitier of this user. This could be an ID, Email Address etc.

### Getting back an error code

Whenever you send an exception to Zappem, we'll send you back a short unique numeric code. It may be useful to display this to your users. On Zappem you can search for the code and jump to the exception straight away.

The error code will be contained in the return of sending the exception to us. Here's an example:

    $Zappem = \Zappem::exception($e)
    ->user(Auth::user()->id, Auth::user()->name, Auth::user()->email)
    ->send();
    
    if($Zappem->success){
      return "Sorry, an error occurred. Please contact development quoting this code: ".$Zappem->code;
    }

  
