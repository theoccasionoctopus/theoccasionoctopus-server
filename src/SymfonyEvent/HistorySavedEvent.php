<?php

namespace App\SymfonyEvent;

use App\Entity\History;
use Symfony\Contracts\EventDispatcher\Event;

class HistorySavedEvent extends  Event
{
    public const NAME = 'app.history.saved';

    /** @var  History */
    protected $history;

    /**
     * HistorySavedEvent constructor.
     * @param History $history
     */
    public function __construct(History $history)
    {
        $this->history = $history;
    }

    /**
     * @return History
     */
    public function getHistory()
    {
        return $this->history;
    }

}
