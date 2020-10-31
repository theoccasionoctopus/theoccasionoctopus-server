<?php

namespace App\FilterParams;

use App\Entity\Account;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\InputBag;

class EventListFilterParams
{

    protected $fromNow = true;

    protected $repositoryQuery = null;

    protected $showDeleted = false;

    protected $showCancelled = true;

    /**
     * EventListFilterParams constructor.
     */
    public function __construct($doctrine, Account $account)
    {

        $this->repositoryQuery = new EventRepositoryQuery($doctrine);
        $this->repositoryQuery->setAccountEvents($account);

    }


    public function build(InputBag $data)
    {

        if ($data->has('eventListFilterDataSubmitted')) {

            // From
            $fromNow = $data->get('fromNow', Null);
            if (!$fromNow) {
                $this->fromNow = false;
            }

            // Deleted
            $this->showDeleted = $data->has('showDeleted');

            // Cancelled
            $this->showCancelled = $data->has('showCancelled');

        }

        // apply to search
        $this->getRepositoryQuery()->setShowCancelled($this->showCancelled);
        $this->getRepositoryQuery()->setShowDeleted($this->showDeleted);

        if ($this->getFromNow()) {
            $this->getRepositoryQuery()->setFrom(new \DateTime('', new \DateTimeZone('UTC')));
        }

    }

    public function getFromNow()
    {
        return $this->fromNow;
    }

    /**
     * @return bool
     */
    public function isShowDeleted()
    {
        return $this->showDeleted;
    }

    /**
     * @return bool
     */
    public function isShowCancelled()
    {
        return $this->showCancelled;
    }



    /**
     * @return EventRepositoryQuery
     */
    public function getRepositoryQuery()
    {
        return $this->repositoryQuery;
    }

}
