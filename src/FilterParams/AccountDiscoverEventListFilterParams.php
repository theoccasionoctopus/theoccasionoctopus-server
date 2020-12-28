<?php

namespace App\FilterParams;

use App\Entity\Account;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountDiscoverEventListFilterParams
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
        $this->repositoryQuery->setAccountDiscoverEvents($account);
        $this->repositoryQuery->setPrivacyLevelOnlyFollowers();
    }


    public function build($data)
    {
        if (isset($data['eventListFilterDataSubmitted'])) {

            // From
            $fromNow = isset($data['fromNow']) ? $data['fromNow'] : 0;
            if (!$fromNow) {
                $this->fromNow = false;
            }

            // Deleted
            $this->showDeleted = isset($data['showDeleted']);

            // Cancelled
            $this->showCancelled = isset($data['showCancelled']);
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
