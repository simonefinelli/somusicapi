<?php

class SOMUSICAPI_CLASS_MobileNotification {

    public static $TYPE = 'mobile';

    private $title;
    private $subtitle;
    private $message;
    private $tickerText;
    private $vibrate;
    private $sound;
    private $largeIcon;
    private $smallIcon;

    public function __construct($title, $subtitle, $message, $tickerText, $vibrate, $sound, $largeIcon, $smallIcon )
    {
        $this->title      = $title;
        $this->subtitle   = $subtitle;
        $this->message    = $message;
        $this->tickerText = $tickerText;
        $this->vibrate    = $vibrate;
        $this->sound      = $sound;
        $this->largeIcon  = $largeIcon;
        $this->smallIcon  = $smallIcon;

    }

    public function send($targets)
    {

        $firebase = new SOMUSICAPI_CLASS_FirebaseSender($this, $targets);
        $firebase->send();

        return $firebase;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getTickerText()
    {
        return $this->tickerText;
    }

    /**
     * @param mixed $tickerText
     */
    public function setTickerText($tickerText)
    {
        $this->tickerText = $tickerText;
    }

    /**
     * @return mixed
     */
    public function getVibrate()
    {
        return $this->vibrate;
    }

    /**
     * @param mixed $vibrate
     */
    public function setVibrate($vibrate)
    {
        $this->vibrate = $vibrate;
    }

    /**
     * @return mixed
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * @param mixed $sound
     */
    public function setSound($sound)
    {
        $this->sound = $sound;
    }

    /**
     * @return mixed
     */
    public function getLargeIcon()
    {
        return $this->largeIcon;
    }

    /**
     * @param mixed $largeIcon
     */
    public function setLargeIcon($largeIcon)
    {
        $this->largeIcon = $largeIcon;
    }

    /**
     * @return mixed
     */
    public function getSmallIcon()
    {
        return $this->smallIcon;
    }

    /**
     * @param mixed $smallIcon
     */
    public function setSmallIcon($smallIcon)
    {
        $this->smallIcon = $smallIcon;
    }

}