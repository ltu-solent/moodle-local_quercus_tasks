<?php

$tasks = array(
    array(
        'classname' => 'local_quercus_tasks\task\add_new_assignments',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\export_grades',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
	array(
        'classname' => 'local_quercus_tasks\task\get_new_modules',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),    
    array(
        'classname' => 'local_quercus_tasks\task\get_new_grades',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
	array(
        'classname' => 'local_quercus_tasks\task\staff_enrolments',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\update_dates',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    )    
);
