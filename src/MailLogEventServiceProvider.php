<?php declare(strict_types=1);

namespace NZTim\MailLog;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use NZTim\Mailer\MessageSent;
use NZTim\MailLog\Listeners\SesBounceListener;
use NZTim\MailLog\Listeners\SesComplaintListener;
use NZTim\MailLog\Listeners\SesDeliveryListener;
use NZTim\MailLog\Listeners\StoreMessageSent;
use NZTim\SES\Events\SesBounce;
use NZTim\SES\Events\SesComplaint;
use NZTim\SES\Events\SesDelivery;

class MailLogEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            StoreMessageSent::class
        ],
        SesDelivery::class => [
            SesDeliveryListener::class,
        ],
        SesBounce::class => [
            SesBounceListener::class,
        ],
        SesComplaint::class => [
            SesComplaintListener::class,
        ],
    ];
}
