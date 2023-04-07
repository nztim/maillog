<?php declare(strict_types=1);

namespace NZTim\MailLog\Listeners;

use NZTim\Mailer\MessageSent;
use NZTim\MailLog\Entry;
use NZTim\MailLog\Persistence\EntryRepo;

class StoreMessageSent
{
    private EntryRepo $entryRepo;

    public function __construct(EntryRepo $entryRepo)
    {
        $this->entryRepo = $entryRepo;
    }

    public function handle(MessageSent $messageSent): Entry
    {
        $data = $messageSent->toArray();
        unset($data['html']);
        unset($data['text']);
        $entry = new Entry(
            Entry::STATUS_SENT,
            $messageSent->messageId(),
            $messageSent->sender(),
            $messageSent->recipient(),
            $messageSent->subject(),
            $messageSent->date(),
            $messageSent->html(),
            [Entry::DATA_KEY_MESSAGE_SENT => $data],
        );
        $id = $this->entryRepo->persist($entry);
        return $this->entryRepo->findById($id);
    }
}
