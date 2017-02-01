<?php

namespace Jeylabs\MailTracker\Events;

use Jeylabs\MailTracker\Model\SentEmail;
use Illuminate\Queue\SerializesModels;

class LinkClickedEvent
{
    use SerializesModels;

    public $sent_email;

    /**
     * Create a new event instance.
     *
     * @param  sent_email  $sent_email
     * @return void
     */
    public function __construct(SentEmail $sent_email)
    {
        $this->sent_email = $sent_email;
    }
}