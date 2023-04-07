<?php declare(strict_types=1);

namespace NZTim\MailLog;

use Illuminate\Support\ServiceProvider;

class MailLogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(MailLogEventServiceProvider::class);
    }

    public function boot()
    {
        $this->commands([
            MigrationCommand::class,
            DeleteOldEntriesCommand::class,
        ]);
    }
}
