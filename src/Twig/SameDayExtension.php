<?php

namespace App\Twig;

use Misd\Linkify\Linkify;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SameDayExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('sameday', [$this, 'sameday'], array()),
        ];
    }

    public function sameday($date1, $date2, $timezone)
    {
        if (get_class($date1) != 'DateTime') {
            return false;
        }
        if (get_class($date2) != 'DateTime') {
            return false;
        }

        return (clone $date1)->setTimezone(new \DateTimeZone($timezone))->format('dmYe') ==
            (clone $date2)->setTimezone(new \DateTimeZone($timezone))->format('dmYe');
    }
}
