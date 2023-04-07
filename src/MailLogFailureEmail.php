<?php declare(strict_types=1);

namespace NZTim\MailLog;

use NZTim\Mailer\AbstractMessage;

class MailLogFailureEmail extends AbstractMessage
{
    public ?string $sender;
    public string $recipient;
    public string $subject;
    public string $view;
    public array $data = [];

    public function __construct(Entry $entry, string $recipient)
    {
        $this->sender = config('mail.address.noreply'); // If null then default sender will be used
        $this->recipient = $recipient;
        $this->subject = 'System email failure (' . $entry->status . ')';
        $this->view = 'emails.email-error';
        $this->data = [
            'id'          => $entry->id,
            'type'        => ucfirst($entry->status),
            'sender'      => $entry->sender,
            'recipient'   => $entry->recipient,
            'subject'     => $entry->subject,
            'description' => $entry->bounceMessage(),
        ];
    }

    public function testLabel(): string
    {
        return 'Email error (SES)';
    }

    public static function test(): MailLogFailureEmail
    {
        $entry = new Entry(
            Entry::STATUS_BOUNCE,
            'abcd12344@mailer2.example.org',
            'sender@example.org',
            'recipient@example.org',
            'A System Message',
            now(),
            'Hello World',
            [],
        );
        $entry->id = 1234;
        $entry->data[Entry::DATA_KEY_SES_BOUNCE]['bounce']['bouncedRecipients'][0]['diagnosticCode'] = 'Mailbox does not exist';
        return new MailLogFailureEmail($entry, 'manager@example.test');
    }
}
