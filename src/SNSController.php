<?php

namespace Jeylabs\MailTracker;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Event;
use Jeylabs\MailTracker\Model\SentEmail;
use Jeylabs\MailTracker\Events\PermanentBouncedMessageEvent;
use Aws\Sns\Message as SNSMessage;
use Aws\Sns\MessageValidator as SNSMessageValidator;
use GuzzleHttp\Client as Guzzle;

class SNSController extends Controller
{
    public function callback(Request $request)
    {
        if(config('app.env') != 'production' && $request->message) {
            // phpunit cannot mock static methods so without making a facade
            // for SNSMessage we have to pass the json data in $request->message
            $message = new SNSMessage(json_decode($request->message, true));
        } else {
            $message = SNSMessage::fromRawPostData();
            $validator = new SNSMessageValidator();
            $validator->validate($message);
        }
        
        switch($message->offsetGet('Type')) {
            case 'SubscriptionConfirmation':
                return $this->confirm_subscription($message);
            case 'Notification':
                return $this->process_notification($message);
        }
    }

    protected function confirm_subscription($message)
    {
        $client = new Guzzle();
        $client->get($message->offsetGet('SubscribeURL'));
        return 'subscription confirmed';
    }

    protected function process_notification($message)
    {
        $message = json_decode($message->offsetGet('Message'));
        switch($message->notificationType) {
            case 'Delivery':
                $this->process_delivery($message);
                break;
            case 'Bounce':
                $this->process_bounce($message);
                if($message->bounce->bounceType == 'Permanent') {
                    foreach($message->bounce->bouncedRecipients as $recipient) {
                        Event::fire(new PermanentBouncedMessageEvent($recipient));
                    }
                }
                break;
            case 'Complaint':
                $this->process_complaint($message);
                foreach($message->complaint->complainedRecipients as $recipient) {
                    Event::fire(new PermanentBouncedMessageEvent($recipient));
                }
                break;
        }
        return 'notification processed';
    }

    protected function process_delivery($message)
    {
        $sent_email = SentEmail::where('message_id',$message->mail->messageId)->first();
        if($sent_email) {
            $meta = $sent_email->meta;
            $meta->put('smtpResponse',$message->delivery->smtpResponse);
            $meta->put('success',true);
            $meta->put('delivered_at',$message->delivery->timestamp);
            $sent_email->meta = $meta;
            $sent_email->save();
        }
    }

    public function process_bounce($message)
    {
        $sent_email = SentEmail::where('message_id',$message->mail->messageId)->first();
        if($sent_email) {
            $meta = $sent_email->meta;
            $current_codes = [];
            if($meta->has('failures')) {
                $current_codes = $meta->get('failures');
            }
            foreach($message->bounce->bouncedRecipients as $failure_details) {
                $current_codes[] = $failure_details;
            }
            $meta->put('failures',$current_codes);
            $meta->put('success',false);
            $sent_email->meta = $meta;
            $sent_email->save();
        }
    }

    public function process_complaint($message)
    {
        $message_id = $message->mail->messageId;
        $sent_email = SentEmail::where('message_id',$message_id)->first();
        if($sent_email) {
            $meta = $sent_email->meta;
            $meta->put('complaint',true);
            $meta->put('success',false);
            $meta->put('complaint_time',$message->complaint->timestamp);
            if(!empty($message->complaint->complaintFeedbackType)) {
                $meta->put('complaint_type',$message->complaint->complaintFeedbackType);
            }
            $sent_email->meta = $meta;
            $sent_email->save();
        }
    }
}
