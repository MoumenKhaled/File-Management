<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Aspect\LoggingAspect;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use App\Http\Controllers\Auth\AuthController;
use App\Services\UserService;
use Go\Aop\Framework\ClassProxy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton(LoggingAspect::class);

        // // Bind aspect to your API controller methods
        // $this->app->bind(AuthController::class, function ($app) {
        //     $compiler = new Compiler(storage_path('app/aop'));
        //     $bind = (new Bind)->bindInterceptors('register', [$app->make(LoggingAspect::class)]);

        //     // Ensure that UserService is injected into AuthController
        //     $userService = $app->make(UserService::class);

        //     return $compiler->newInstance(AuthController::class, [$userService], $bind);


        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
