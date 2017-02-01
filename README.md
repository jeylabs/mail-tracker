# MailTracker

MailTracker will hook into all outgoing emails from Laravel and inject a tracking code into it.  It will also store the rendered email in the database.  There is also an interface to view sent emails.

## NOTE: For Laravel < 5.3.23 

## Install (Laravel)

Via Composer

``` bash
$ composer require jeylabs/mail-tracker
```

Add the following to the providers array in config/app.php:

``` php
Jeylabs\MailTracker\MailTrackerServiceProvider::class,
```

Publish the config file and migration
``` bash
$ php artisan vendor:publish
```

Run the migration
``` bash
$ php artisan migrate
```

## Usage

Once installed, all outgoing mail will be logged to the database.  The following config options are available in config/mail-tracker.php:

* **track-open**: set to true to inject a tracking pixel into all outgoing html emails.
* **track-click**: set to true to rewrite all anchor href links to include a tracking link. The link will take the user back to your website which will then redirect them to the final destination after logging the click.
* **expire-days**: How long in days that an email should be retained in your database.  If you are sending a lot of mail, you probably want it to eventually expire.  Set it to zero to never purge old emails from the database.
* **route**: The route information for the tracking URLs.  Set the prefix and middlware as desired.
* **date-format**: You can define the format to show dates in the Admin Panel.

## Events

When an email is sent, viewed, or a link is clicked, its tracking information is counted in the database using the Jeylabs\MailTracker\Model\SentEmail model. You may want to do additional processing on these events, so an event is fired in these cases:

* Jeylabs\MailTracker\Events\EmailSentEvent
* Jeylabs\MailTracker\Events\ViewEmailEvent
* Jeylabs\MailTracker\Events\LinkClickedEvent

If you are using the Amazon SNS notification system, an event is fired when you receive a permanent bounce.  You may want to mark the email as bad or remove it from your database.

* Jeylabs\MailTracker\Events\PermanentBouncedMessageEvent

To install an event listener, you will want to create a file like the following:

``` php
<?php

namespace App\Listeners;

use Jeylabs\MailTracker\Events\ViewEmailEvent;

class EmailViewed
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ViewEmailEvent  $event
     * @return void
     */
    public function handle(ViewEmailEvent $event)
    {
        // Access the model using $event->sent_email...
    }
}
```

``` php
<?php

namespace App\Listeners;

use Jeylabs\MailTracker\Events\PermanentBouncedMessageEvent;

class BouncedEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PermanentBouncedMessageEvent  $event
     * @return void
     */
    public function handle(PermanentBouncedMessageEvent $event)
    {
        // Access the email address using $event->email_address...
    }
}
```

Then you must register the event in your \App\Providers\EventServiceProvider $listen array:

``` php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'Jeylabs\MailTracker\Events\ViewEmailEvent' => [
        'App\Listeners\EmailViewed',
    ],
    'Jeylabs\MailTracker\Events\PermanentBouncedMessageEvent' => [
        'App\Listeners\BouncedEmail',
    ],
];
```

### Passing data to the event listeners

Often times you may need to link a sent email to another model.  The best way to handle this is to add a header to your outgoing email that you can retrieve in your event listener.  Here is an example:

``` php
/**
 * Send an email and do processing on a model with the email
 */
\Mail::send('email.test', [], function ($message) use($email, $subject, $name, $model) {
    $message->from('info@jeylabs.com', 'From Name');
    $message->sender('info@jeylabs.com', 'Sender Name');
    $message->to($email, $name);
    $message->subject($subject);

    // Create a custom header that we can later retrieve
    $message->getHeaders()->addTextHeader('X-Model-ID',$model->id);
});
```

and then in your event listener:

```
public function handle(EmailSentEvent $event)
{
    $tracker = $event->sent_email;
    $model_id = $event->getHeader('X-Model-ID');
    $model = Model::find($model_id);
    // Perform your tracking/linking tasks on $model knowing the SentEmail object
}
```

Note that the headers you are attaching to the email are actually going out with the message, so do not store any data that you wouldn't want to expose to your email recipients.

## Amazon SES features

If you use Amazon SES, you can add some additional information to your tracking.  To set up the SES callbacks, first set up SES notifications under your domain in the SES control panel.  Then subscribe to the topic by going to the admin panel of the notification topic and creating a subscription for the URL you copied from the admin page.  The system should immediately respond to the subscription request.  If you like, you can use multiple subscriptions (i.e. one for delivery, one for bounces).  See above for events that are fired on a failed message.
