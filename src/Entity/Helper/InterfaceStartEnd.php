<?php

namespace App\Entity\Helper;

interface InterfaceStartEnd
{
    public function isAllDay(): bool;
    public function getStart($timezone = null);
    public function getEnd($timezone = null);
}
