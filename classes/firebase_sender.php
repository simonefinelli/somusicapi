<?php

class SOMUSICAPI_CLASS_FirebaseSender {

    private $notification;
    private $targets;

    public function __construct($notification, $targets)
    {
        $this->notification = $notification;
        $this->targets      = $targets;
    }

    public function send() {

        // API access key from Google API's Console
        define( 'API_ACCESS_KEY', 'AAAALwDJVy0:APA91bFrMdsro5rbqEW...sijMaq3Ao01p8CzPy-qEPAceNkLmM9jMfKT7lQrV5jSV');

        $registrationIds = $this->targets;

        // prep the bundle
        $msg = array
        (
            'message' 	=> $this->notification->getMessage(),
            'title'		=> $this->notification->getTitle(),
            'subtitle'	=> $this->notification->getSubtitle(),
            'tickerText'=> $this->notification->getTickerText(),
            'vibrate'	=> $this->notification->getVibrate(),
            'sound'		=> $this->notification->getSound(),
            'largeIcon'	=> $this->notification->getLargeIcon(),
            'smallIcon'	=> $this->notification->getSmallIcon()
        );

        $fields = array
        (
            'registration_ids' 	=> $registrationIds,
            'data'			=> $msg
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        try
        {
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );
        }
        catch (Exception $ex)
        {
            return $ex->getMessage();
        }


       return $result;
    }

}
