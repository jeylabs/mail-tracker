<?php

namespace Jeylabs\MailTracker;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container as Application;
class MailTrackerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishMigrations();

        $this->registerSwiftPlugin();

        $this->installRoutes();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings($this->app);
    }

    protected function registerBindings(Application $app)
    {
        $app->alias('MailTracker', MailTracker::class);
    }

    protected function registerSwiftPlugin()
    {
        $this->app['mailer']->getSwiftMailer()->registerPlugin(new MailTracker());
    }

    protected function publishConfig(){
        $source = __DIR__ . '/../config/mail-tracker.php';
        $this->publishes([$source => config_path('mail-tracker.php')]);
        $this->mergeConfigFrom($source, 'mail-tracker');
    }

    protected function publishMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->publishes([
            __DIR__.'/../migrations/2016_03_01_193027_create_sent_emails_table.php' => database_path('migrations/2016_03_01_193027_create_sent_emails_table.php')
        ], 'config');
        $this->publishes([
            __DIR__.'/../migrations/2016_09_07_193027_create_sent_emails_Url_Clicked_table.php' => database_path('migrations/2016_09_07_193027_create_sent_emails_Url_Clicked_table.php')
        ], 'config');
        $this->publishes([
            __DIR__.'/../migrations/2016_11_10_213551_add-message-id-to-sent-emails-table.php' => database_path('migrations/2016_11_10_213551_add-message-id-to-sent-emails-table.php')
        ], 'config');

    }

    protected function installRoutes()
    {
    $config = $this->app['config']->get('mail-tracker.route', []);
    $config['namespace'] = 'Jeylabs\MailTracker';
        Route::group($config, function()
        {
            Route::get('t/{hash}', 'MailTrackerController@getT')->name('mailTracker_t');
            Route::get('l/{url}/{hash}', 'MailTrackerController@getL')->name('mailTracker_l');
            Route::post('sns', 'SNSController@callback')->name('mailTracker_SNS');
        });
    }
}
