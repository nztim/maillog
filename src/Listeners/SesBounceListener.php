<?php declare(strict_types=1);

namespace NZTim\MailLog\Listeners;

use NZTim\Mailer\Mailer;
use NZTim\MailLog\Persistence\EntryRepo;
use NZTim\SES\Events\SesBounce;
use NZTim\WhatIsMyIp\WhatIsMyIp;

class SesBounceListener
{
    private WhatIsMyIp $myIp;
    private EntryRepo $entryRepo;
    private NotifyFailure $notify;

    public function __construct(WhatIsMyIp $myIp, EntryRepo $entryRepo, NotifyFailure $notify)
    {
        $this->myIp = $myIp;
        $this->entryRepo = $entryRepo;
        $this->notify = $notify;
    }

    public function handle(SesBounce $sesBounce): void
    {
        // Skip processing if sent from another system ------------------------
        if ($sesBounce->sesMail()->sourceIp() !== $this->myIp->get()) {
            return;
        }
        // Log event ----------------------------------------------------------
        $message = [
            'Bounce',
            $sesBounce->sesMail()->recipient(),
            $sesBounce->sesMail()->subject(),
            $sesBounce->diagnosticCode(),
            Mailer::ID_HEADER . ':' . $sesBounce->sesMail()->header(Mailer::ID_HEADER),
        ];
        log_info('ses', implode(' | ', $message));
        // Update MailLog Entry  ----------------------------------------------
        $entry = $this->entryRepo->findByMessageId($sesBounce->sesMail()->header(Mailer::ID_HEADER));
        if (!$entry) {
            log_warning('ses', 'SesBounceListener failure: cannot find Entry for message id: ' . $sesBounce->sesMail()->header(Mailer::ID_HEADER));
            return;
        }
        if ($sesBounce->possibleAutoresponderFailure() && $entry->isDelivered()) {
            log_warning('mail-bounce', "Auto-responder soft-fail, left as delivered: recipient: {$entry->recipient} | subject: {$entry->subject}");
            return;
        }
        $entry = $this->entryRepo->setBounced($entry, $sesBounce->data);
        // Notify sender for hard failures and complaints ---------------------
        $this->notify->handle($entry);
    }
}
