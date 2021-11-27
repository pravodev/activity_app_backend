<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        DB::listen(function ($query) {
            error_log($query->sql);
            
        });

        $models = [
            'Activity',
            'History',
            'Setting',
            'Category',
            'MediaGallery',
            'ApplicationLog',
        ];

        //binding repository
        foreach ($models as $key => $value) {
            if(is_array($value)) {
                if(Arr::exists($value, "repository")) {
                    $this->app->singleton("App\Repositories\Contracts\\{$key}RepositoryContract", $value["repository"]);
                } else {
                    $this->app->singleton("App\Repositories\Contracts\\{$key}RepositoryContract", "App\Repositories\Implementations\\{$key}RepositoryImplementation");
                }
            } else {
                $this->app->singleton("App\Repositories\Contracts\\{$value}RepositoryContract", "App\Repositories\Implementations\\{$value}RepositoryImplementation");
            }
            
        }

        //binding services
        foreach ($models as $key => $value) {
            if(is_array($value)) {
                if(Arr::exists($value, "service")) {
                    $this->app->singleton("App\Services\Contracts\\{$key}ServiceContract", $value["service"]);
                } else {
                    $this->app->singleton("App\Services\Contracts\\{$key}ServiceContract", "App\Services\Implementations\\{$key}ServiceImplementation");
                }
            } else {
                $this->app->singleton("App\Services\Contracts\\{$value}ServiceContract", "App\Services\Implementations\\{$value}ServiceImplementation");
            }
            
        }
    }
}
