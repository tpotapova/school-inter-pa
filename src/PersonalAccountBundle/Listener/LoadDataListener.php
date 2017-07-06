<?php
/**
 * Created by PhpStorm.
 * User: Potap
 * Date: 11.11.2016
 * Time: 18:11
 */

namespace PersonalAccountBundle\Listener;

use AncaRebeca\FullCalendarBundle\Event\CalendarEvent;
use PersonalAccountBundle\Entity\Event as Event;

class LoadDataListener
{
    /**
     * @param CalendarEvent $calendarEvent
     *
     * @return EventInterface[]
     */
    public function loadData(CalendarEvent $calendarEvent)
    {
        $startDate = $calendarEvent->getStartDatetime();
        $endDate = $calendarEvent->getEndDatetime();
        $filters = $calendarEvent->getFilters();

    }
}