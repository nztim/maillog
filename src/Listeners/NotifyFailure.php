<?php declare(strict_types=1);

namespace NZTim\MailLog\Listeners;

use NZTim\MailLog\Entry;
use NZTim\MailLog\MailLogFailureEmail;
use NZTim\Queue\QueueManager;

// All the logic for notification in the case of a hard failure
// This needs to be an event containing all the info needed for an email
class NotifyFailure
{
    private QueueManager $qm;

    public function __construct(QueueManager $qm)
    {
        $this->qm = $qm;
    }

    public function handle(Entry $entry): void
    {
        $recipient = $entry->sender;
        $backupRecipient = filter_var(config('logger.email.to'), FILTER_VALIDATE_EMAIL) ? config('logger.email.to') : '';
        // Do not send notifications to noreply address
        if (str_starts_with($recipient, 'noreply@')) {
            $recipient = $backupRecipient;
        }
        // Do not send notifications to the address that bounced (prevent loop)
        if ($recipient === $entry->recipient) {
            $recipient = $backupRecipient;
        }
        // Don't send anything if the backup recipient is the failure, or there is no backup recipient
        if ($entry->recipient === $backupRecipient) {
            log_warning('fatal_email', "Message to log recipient ({$backupRecipient}) failed! ");
            return;
        }
        if (!$recipient) {
            log_warning('fatal_email', "Unable to send error notification and no backup recipient configured!");
            return;
        }
        log_info('bounce_notify', 'bounced:' . $entry->recipient . ' | notify:' . $recipient . ' | type: ' . $entry->status);
        $this->qm->add(new MailLogFailureEmail($entry, $recipient));
    }
}
