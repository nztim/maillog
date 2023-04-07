<?php declare(strict_types=1);

namespace NZTim\MailLog;

use NZTim\Mailer\Mailer;

class MailLogFailureEmailHandler
{
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(MailLogFailureEmail $message)
    {
        $this->mailer->send($message);
    }
}
