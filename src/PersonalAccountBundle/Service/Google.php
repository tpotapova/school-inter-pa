<?php
/**
 * Created by PhpStorm.
 * User: Potap
 * Date: 10.11.2016
 * Time: 10:02
 */

namespace PersonalAccountBundle\Service;

class Google
{
    /* @var \Google_Client */
    private $client;

    /* @var \Google_Service_Calendar */
    private $calendar;


    public function __construct()
    {
        $this->client = new \Google_Client();
    }
    public function loadKey()
    {
        $this->client->setDeveloperKey( "AIzaSyDtx0fHvtXWsQ7jypnx6w35MtRQGHTtae8");
    }
    public function getCalendar()
    {
        if (!$this->calendar) {
            $this->calendar = new \Google_Service_Calendar($this->client);
            $this->loadKey();
        }

        return $this->calendar;
    }


}