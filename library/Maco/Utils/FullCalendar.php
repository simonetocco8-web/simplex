<?php

class Maco_Utils_FullCalendar
{
    protected static $baseUrl = false;
    static public function formatTasks($tasks)
    {
        $events = array();

        self::$baseUrl || self::$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

        foreach($tasks as $task)
        {
            $event = array(
                'id' => $task['task_id'],
                'title' => $task['who'] . ' ' . Model_Task::getWhatForValue($task['what']) . ' (' . $task['company'] . ')',
                'start' => $task['when'],
                'allDay' => false,
                'url' => self::$baseUrl . '/tasks/detail/id/' . $task['task_id'],
                //'end' => $task['when'],
            );

            if($task['what'] == 3)
            {
                $event['backgroundColor'] = '#fdd';
            }

            if($task['time_expected'])
            {
                // controlliamo se � un intero o se dobbiamo calcolare i minuti
                $h = floor($task['time_expected']);
                $dec = (float)$task['time_expected'] - $h;
                $m = floor(60 * $dec);
                $interval_string = 'PT' . $h . 'H' . $m . 'M';
                $start = new DateTime($task['when']);
                $end = $start->add(new DateInterval($interval_string));
                $event['end'] = $end->format('Y-m-d H:i:s');
            }

            $event['description'] = strip_tags(Model_Task::getFormattedTask($task));

            $events[] = $event;
        }

        return $events;
    }
}