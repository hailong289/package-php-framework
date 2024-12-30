<?php

namespace Hola\Core;

class Date
{
    private $date;
    private $format = 'Y-m-d H:i:s';
    private $timezone = TIMEZONE;

    /**
     * Date constructor.
     */
    public static function init()
    {
        return new Date();
    }

    /**
     * clear timezone
     */
    private function clearTimezone()
    {
        date_default_timezone_set(TIMEZONE); // clear timezone when success
    }

    /**
     * set timezone
     */
    public function setTimezone($timezone = TIMEZONE)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * get date
     */
    public function get()
    {
        $this->date = date($this->format, strtotime($this->date));
        return $this->date;
    }

    /**
     * get timestamp
     */
    public function getTimestamp()
    {
        $timestamp = strtotime($this->date);
        return $timestamp;
    }

    /**
     * set date
     */
    public function set($date)
    {
        date_default_timezone_set($this->timezone);
        $this->date = date($date);
        $this->clearTimezone();
        return $this;
    }

    /**
     * set timestamp
     */
    public function setTimestamp($timestamp)
    {
        date_default_timezone_set($this->timezone);
        $timestamp = strtotime($timestamp);
        $this->date = date($this->format, $timestamp);
        $this->clearTimezone();
        return $this;
    }

    /**
     * now
     */
    public function now()
    {
        date_default_timezone_set($this->timezone);
        $this->date = date($this->format);
        $this->clearTimezone();
        return $this;
    }

    /**
     * @param string $format
     * format
     */
    public function format($format = 'Y-m-d H:i:s')
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param number $number
     * add day
     */
    public function addDay($number)
    {
        $newdate = strtotime("+$number day", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub day
     */
    public function subDay($number)
    {
        $newdate = strtotime("-$number day", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * add week
     */
    public function addWeek($number)
    {
        $newdate = strtotime("+$number week", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub week
     */
    public function subWeek($number)
    {
        $newdate = strtotime("-$number week", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * add month
     */
    public function addMonth($number)
    {
        $newdate = strtotime("+$number month", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub month
     */
    public function subMonth($number)
    {
        $newdate = strtotime("-$number month", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * add year
     */
    public function addYear($number)
    {
        $newdate = strtotime("+$number year", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub year
     */
    public function subYear($number)
    {
        $newdate = strtotime("-$number year", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * add hour
     */
    public function addHour($number)
    {
        $newdate = strtotime("+$number hour", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub hour
     */
    public function subHour($number)
    {
        $newdate = strtotime("-$number hour", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * add minute
     */
    public function addMinute($number)
    {
        $newdate = strtotime("+$number minute", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub minute
     */
    public function subMinute($number)
    {
        $newdate = strtotime("-$number minute", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * add second
     */
    public function addSeconds($number)
    {
        $newdate = strtotime("+$number seconds", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    /**
     * @param number $number
     * sub second
     */
    public function subSeconds($number)
    {
        $newdate = strtotime("-$number seconds", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }
}