<?php declare(strict_types=1);

namespace NZTim\MailLog;

use Carbon\Carbon;

class Entry
{
    public ?int $id;
    public string $status;
    public string $messageId;
    public string $sender;
    public string $recipient;
    public string $subject;
    public Carbon $date;
    public string $content;
    public array $data;
    public Carbon $created;
    public Carbon $updated;

    public function __construct(
        string $status,
        string $messageId,
        string $sender,
        string $recipient,
        string $subject,
        Carbon $date,
        string $content,
        array $data
    ) {
        $this->id = null;
        $this->status = $status;
        $this->messageId = $messageId;
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->date = $date;
        $this->content = $content;
        $this->data = $data;
        $this->created = now();
        $this->updated = now();
    }

    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_BOUNCE = 'bounce';
    public const STATUS_COMPLAINT = 'complaint';

    public static function statusSelect(string $first = null): array
    {
        $types = [
            Entry::STATUS_SENT      => 'Sent',
            Entry::STATUS_DELIVERED => 'Delivered',
            Entry::STATUS_BOUNCE    => 'Bounce',
            Entry::STATUS_COMPLAINT => 'Spam',
        ];
        return $first ? ['' => $first] + $types : $types;
    }

    public const DATA_KEY_MESSAGE_SENT = 'message_sent';
    public const DATA_KEY_SES_BOUNCE = 'ses_bounce';
    public const DATA_KEY_SES_COMPLAINT = 'ses_complaint';

    public function isSent(): bool
    {
        return $this->status === Entry::STATUS_SENT;
    }

    public function isDelivered(): bool
    {
        return $this->status === Entry::STATUS_DELIVERED;
    }

    public function isBounce(): bool
    {
        return $this->status === Entry::STATUS_BOUNCE;
    }

    public function isComplaint(): bool
    {
        return $this->status === Entry::STATUS_COMPLAINT;
    }

    public function bounceMessage(): string
    {
        return $this->data[Entry::DATA_KEY_SES_BOUNCE]['bounce']['bouncedRecipients'][0]['diagnosticCode'] ?? '';
    }
}
