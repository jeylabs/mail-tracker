<?php

namespace Jeylabs\MailTracker\Model;

use Illuminate\Database\Eloquent\Model;
// use Model\SentEmail;

class SentEmailUrlClicked extends Model
{
    protected $table = 'sent_emails_url_clicked';

    protected $fillable = [
    	'sent_email_id',
    	'url',
        'hash',
    	'clicks',
    ];

    public function email()
    {
      return $this->belongsTo(SentEmail::class,'sent_email_id');
    }
}
