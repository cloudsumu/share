<?php

return [
    'driver'      => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key'       => config('constants.android_push_key', 'AAAAl4Hyffc:APA91bEGmABGLK7PPIo2epUGNrZic3DMmVHOu8S4kOkar-HfVyuCNIfl82atIGWIU5APhZ2EwPCxb6LKO6SH9HnwDBYTyGZwfqJ6WMx_q0ppQyxUfxQLX_1HJ7YrnCKIbeClOkC9Kojp'),
        'sender_id'        => config('constants.android_sender_key', '650720214519'),
        'server_send_url'  => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout'          => 30.0, // in second
    ],
];
