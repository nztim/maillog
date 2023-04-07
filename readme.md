# Mail Log

* Listen to Mailer and record details and content of messages sent.
* Listen to SES and update status of messages.
* Provide notifications for failures.

### Configuration

* Configure database connection: `database.maillog`;
* Add Service Provider `NZTim\MailLog\MailLogServiceProvider::class,`
* Create and run migration `php artisan maillog:migration && php artisan migrate`
* Add scheduler entry to console Kernel.php: `$schedule->command(DeleteOldEntriesCommand::class)->dailyAt('4:00');`
* Add noreply address to config `mail.address.noreply`.
* Check config entry for `logger.email.to` is set, this is used as a backup recipient for failure notifications.
* Provide an error notification email view: `emails.email-error`.

