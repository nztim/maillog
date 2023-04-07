<?php declare(strict_types=1);

namespace NZTim\MailLog\Listeners;

use NZTim\Mailer\Mailer;
use NZTim\MailLog\Persistence\EntryRepo;
use NZTim\SES\Events\SesComplaint;
use NZTim\WhatIsMyIp\WhatIsMyIp;

class SesComplaintListener
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

    public function handle(SesComplaint $sesComplaint): void
    {
        // Skip processing if sent from another system ------------------------
        if ($sesComplaint->sesMail()->sourceIp() !== $this->myIp->get()) {
            return;
        }
        // Log event ----------------------------------------------------------
        $message = [
            'Complaint',
            $sesComplaint->sesMail()->recipient(),
            $sesComplaint->sesMail()->subject(),
            $sesComplaint->complaintType(),
            Mailer::ID_HEADER . ':' . $sesComplaint->sesMail()->header(Mailer::ID_HEADER),
        ];
        log_info('ses', implode(' | ', $message));
        // Update MailLog Entry  ----------------------------------------------
        $entry = $this->entryRepo->findByMessageId($sesComplaint->sesMail()->header(Mailer::ID_HEADER));
        if (!$entry) {
            log_warning('ses', 'SesComplaintListener failure: cannot find Entry for message id: ' . $sesComplaint->sesMail()->header(Mailer::ID_HEADER));
            return;
        }
        $entry = $this->entryRepo->setComplaint($entry, $sesComplaint->data);
        // Notify sender for hard failures and complaints ---------------------
        $this->notify->handle($entry);
    }
}
