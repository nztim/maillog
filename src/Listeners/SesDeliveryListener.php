<?php declare(strict_types=1);

namespace NZTim\MailLog\Listeners;

use NZTim\Mailer\Mailer;
use NZTim\MailLog\Persistence\EntryRepo;
use NZTim\SES\Events\SesDelivery;
use NZTim\WhatIsMyIp\WhatIsMyIp;

class SesDeliveryListener
{
    private WhatIsMyIp $myIp;
    private EntryRepo $entryRepo;

    public function __construct(WhatIsMyIp $myIp, EntryRepo $entryRepo)
    {
        $this->myIp = $myIp;
        $this->entryRepo = $entryRepo;
    }

    public function handle(SesDelivery $sesDelivery): void
    {
        // Skip processing if sent from another system ------------------------
        if ($sesDelivery->sesMail()->sourceIp() !== $this->myIp->get()) {
            return;
        }
        // Log event ----------------------------------------------------------
        $message = [
            'Delivery',
            $sesDelivery->sesMail()->recipient(),
            $sesDelivery->sesMail()->subject(),
            Mailer::ID_HEADER . ':' . $sesDelivery->sesMail()->header(Mailer::ID_HEADER),
        ];
        log_info('ses', implode(' | ', $message));
        // Update MailLog Entry  ----------------------------------------------
        $entry = $this->entryRepo->findByMessageId($sesDelivery->sesMail()->header(Mailer::ID_HEADER));
        if (!$entry) {
            $info = [
                'sender'    => $sesDelivery->sesMail()->headerFrom(),
                'recipient' => $sesDelivery->sesMail()->recipient(),
                'subject'   => $sesDelivery->sesMail()->subject(),
            ];
            log_warning('ses', 'SesDeliveryListener failure: cannot find Entry for message id: ' . $sesDelivery->sesMail()->header(Mailer::ID_HEADER), $info);
            return;
        }
        $this->entryRepo->setDelivered($entry);
    }
}
