<?php

namespace System\Core;

class Date
{
    private $date;
    private $format = 'Y-m-d H:i:s';

    public static function init($timezone = TIMEZONE)
    {
        date_default_timezone_set($timezone);
        return new Date();
    }

    public function get()
    {
        $this->date = date($this->format, strtotime($this->date));
        date_default_timezone_set(TIMEZONE); // clear timezone when success
        return $this->date;
    }

    public function set($date)
    {
        $this->date = date($date);
        return $this;
    }

    public function now()
    {
        $this->date = date($this->format);
        return $this;
    }

    public function format($format = 'Y-m-d H:i:s')
    {
        $this->format = $format;
        return $this;
    }

    public function addDay($number)
    {
        $newdate = strtotime("+$number day", strtotime($this->date));
        $this->date = date($this->format, $add_date);
        return $this;
    }

    public function subDay($number)
    {
        $newdate = strtotime("-$number day", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function addWeek($number)
    {
        $newdate = strtotime("+$number week", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function subWeek($number)
    {
        $newdate = strtotime("-$number week", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function addMonth($number)
    {
        $newdate = strtotime("+$number month", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function subMonth($number)
    {
        $newdate = strtotime("-$number month", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function addYear($number)
    {
        $newdate = strtotime("+$number year", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function subYear($number)
    {
        $newdate = strtotime("-$number year", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function addHour($number)
    {
        $newdate = strtotime("+$number hour", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function subHour($number)
    {
        $newdate = strtotime("-$number hour", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function addMinute($number)
    {
        $newdate = strtotime("+$number minute", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function subMinute($number)
    {
        $newdate = strtotime("-$number minute", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function addSeconds($number)
    {
        $newdate = strtotime("+$number seconds", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }

    public function subSeconds($number)
    {
        $newdate = strtotime("-$number seconds", strtotime($this->date));
        $this->date = date($this->format, $newdate);
        return $this;
    }
}