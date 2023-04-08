<?php

namespace App\Notifications;

use Benwilkins\FCM\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FcmNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $title, $body, $url,$key,$icon ,$request_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $body, $url = null,$key= 'INCOMING_PUSH',$icon,$request_id = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->key = $key;
        $this->icon = $icon;
        $this->request_id = $request_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable) 
{
    $message = new FcmMessage();
    $message->
    setHeaders([
        'project_id'    =>  config('constants.android_sender_key')   // FCM sender_id
    ])->
    // content([
    //     'title'        => $this->title, 
    //     'body'         => $this->body, 
    //     'sound'        => '', // Optional 
    //     'icon'         => '', // Optional
    //     'click_action' => '' // Optional
    // ])->
    data([
        'message'         => $this->body, 
        'key' =>  $this->key,// Optional
        'request_id' =>  $this->request_id,// Optional
    ])->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.
    return $message;
}

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
