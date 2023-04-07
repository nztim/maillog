<?php declare(strict_types=1);

namespace NZTim\MailLog;

use Illuminate\Console\Command;
use NZTim\MailLog\Persistence\EntryRepo;

class DeleteOldEntriesCommand extends Command
{
    protected $signature = 'maillog:delete-old-entries';
    protected $description = 'Delete old sent entries in mail_log table';

    public function handle()
    {
        $entryRepo = app(EntryRepo::class);
        $entries = $entryRepo->findOld();
        foreach ($entries as $entry) {
            $entryRepo->delete($entry);
        }
    }
}
