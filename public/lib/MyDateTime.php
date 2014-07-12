<?php

/**
 * Class MyDateTime
 *
 * @package \
 * @author Ionut DINU <idinu@techlang.ro>
 * @version GIT: $Id:$
 */
class MyDateTime extends DateTime
{
    /** @var integer */
    protected $realDay;

    /**
     * Method __construct
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     *
     * @throws \Exception
     * @return DateTime
     * @link http://php.net/manual/en/datetime.construct.php
     */
    public function __construct($time = 'now', $timezone = null)
    {
        if (!is_null($timezone) && !$timezone instanceof \DateTimeZone) {
            throw new \Exception('DateTime::__construct() expects parameter 2 to be DateTimeZone, ' . gettype($timezone) . ' given');
        }

        parent::__construct($time, $timezone);
        $this->realDay = $this->format('j');
    }

    /**
     * Parse a string into a new DateTime object according to the specified format
     *
     * @param string $format Format accepted by date().
     * @param string $time String representing the time.
     * @param \DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
     *
     * @throws \Exception
     * @return DateTime
     * @link http://php.net/manual/en/datetime.createfromformat.php
     */
    public static function createFromFormat($format, $time, $timezone = null)
    {
        if (is_null($timezone)) {
            $object = parent::createFromFormat($format, $time);
        }
        else {
            if (!$timezone instanceof \DateTimeZone) {
                $errorMessage = "DateTime::createFromFormat() expects parameter 3 to be DateTimeZone, " . gettype($timezone) . " given";
                trigger_error($errorMessage, E_USER_WARNING);
                return false;
            }
            $object = parent::createFromFormat($format, $time, $timezone);
        }

        return new self($object->format('Y-m-d H:i:s'), $timezone);
    }

    /**
     * Adds an amount of days, months, years, hours, minutes and seconds to a DateTime object
     *
     * @param \DateInterval $interval
     * @return DateTime|false
     * @link http://php.net/manual/en/datetime.add.php
     */
    public function add($interval)
    {
        if (!$interval instanceof \DateInterval) {
            $errorMessage = "DateTime::add() expects parameter 1 to be DateInterval, " . gettype($interval) . " given";
            trigger_error($errorMessage, E_USER_WARNING);
            return false;
        }

        if (0 == $interval->m) {
            // shortcut if we don't have months to add (these are the tricky ones)
            return parent::add($interval);
        }
        else {
            // add the years
            if (0 != $interval->y) {
                $yearInterval = new \DateInterval('P'.$interval->y.'Y');
                parent::add($yearInterval);
            }

            // now comes the tricky part
            $monthInterval = new \DateInterval('P'.$interval->m.'M');
            $this->modify('first day of this month');
            parent::add($monthInterval);
            $lastDayOfTheMonth = $this->format('t');
            $payDay = min($this->realDay, $lastDayOfTheMonth);
            $this->modify("" . ($payDay - 1) . " days");

            // and now the rest of the interval (if any)
            $rest = 'P';
            if (0 != $interval->d) {
                $rest .= $interval->d . 'D';
            }
            $rest .= 'T';
            if (0 != $interval->h) {
                $rest .= $interval->h . 'H';
            }
            if (0 != $interval->i) {
                $rest .= $interval->i . 'M';
            }
            if (0 != $interval->s) {
                $rest .= $interval->s . 'S';
            }

            $rest = trim($rest, 'T');
            if ('P' != $rest) {
                $restInterval = new \DateInterval($rest);
                parent::add($restInterval);

                // since we added days or time to our DateTime object the initial day is no longer relevant
                $this->forgetReferenceDay();
            }
        }

        return $this;
    }

    /**
     * Cause it to reset the reference day.
     * This object no longer remembers the initial date from construction.
     *
     * @return $this
     */
    public function forgetReferenceDay()
    {
        $this->realDay = $this->format('j');
        return $this;
    }
}

/*
# \TechLang\DateTime
[![Build Status](https://travis-ci.org/techlang/date.svg?branch=master)](https://travis-ci.org/techlang/date)
[![Coverage Status](https://img.shields.io/coveralls/techlang/date.svg)](https://coveralls.io/r/techlang/date)
[![Latest Stable Version](https://poser.pugx.org/techlang/date/v/stable.svg)](https://packagist.org/packages/techlang/date)
[![Latest Unstable Version](https://poser.pugx.org/techlang/date/v/unstable.svg)](https://packagist.org/packages/techlang/date)
[![Total Downloads](https://poser.pugx.org/techlang/date/downloads.svg)](https://packagist.org/packages/techlang/date)
[![License](https://poser.pugx.org/techlang/date/license.svg)](https://packagist.org/packages/techlang/date)

## Introduction

Enhance DateTime objects to add calendar months.
What this means is that it will keep the day of the month and only modify month number.
If the resulting month does not have that day (for example 31 February) it will use the highest day available for that month.

## Examples

* adding a month 6 times; Jan. 31st scenario
```php
$date = new \TechLang\DateTime('2000-01-31');
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2000-02-29
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2000-03-31
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2000-04-30
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2000-05-31
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2000-06-30
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2000-07-31
// and so on
```

* adding a month 6 times; Jan. 30th scenario
```php
$date = new \TechLang\DateTime('2001-01-30');
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-02-28
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-03-30
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-04-30
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-05-30
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-06-30
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-07-30
// and so on
```

* adding 2 months
```php
$date = \TechLang\DateTime::createFromFormat('Y-m-d', '2000-12-31');
$date->add(new \DateInterval('P2M'));
echo $date->format('Y-m-d');
// this will output: 2001-02-28
```

* add anything lower than month and you loose the initial date
```php
$date = new \TechLang\DateTime('2000-11-30');
$date->add(new \DateInterval('P1M2D'));
echo $date->format('Y-m-d'); // -> 2001-01-01

// because we added 2 days the date is now 2000-01-01 and the original day of 30 is lost
$date->add(new \DateInterval('P1M'));
echo $date->format('Y-m-d'); // -> 2001-02-01
```

## Future development
* implement `sub` method


*/