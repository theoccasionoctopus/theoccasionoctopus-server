<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\User;
use App\Entity\UserManageAccount;
use App\FilterParams\AccountDiscoverEventListFilterParams;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccountManageController extends BaseController
{

    /** @var  Account */
    protected $account;


    protected function setUpAccountManage($account_username, Request $request)
    {
        // If no user don't even bother
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if (!($user instanceof User)) {
            throw new  AccessDeniedException('You must log in first!');
        }

        // Load account
        $doctrine = $this->getDoctrine();
        $accountLocal = $doctrine->getRepository(AccountLocal::class)->findOneByUsernameCanonical(Library::makeAccountUsernameCanonical($account_username));
        if (!$accountLocal || $accountLocal->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->account = $accountLocal->getAccount();

        // Check user has access
        $userManagesAccount = $doctrine->getRepository(UserManageAccount::class)->findOneBy(['user'=>$user,'account'=>$this->account]);
        if (!$userManagesAccount) {
            throw new  AccessDeniedException('You can not manage this account!');
        }

        // Now other stuff
        $this->setUp($request);
    }


    protected function getTemplateVariables($vars = array())
    {
        $vars['account'] = $this->account;
        return parent::getTemplateVariables($vars);
    }

    public function index($account_username, Request $request)
    {
        $this->setUpAccountManage($account_username, $request);

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setShowDeleted(false);
        $repositoryQuery->setShowCancelled(false);
        $repositoryQuery->setLimit(3);
        $eventOccurrences = $repositoryQuery->getEventOccurrences();

        $discoverRepositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $discoverRepositoryQuery->setAccountDiscoverEvents($this->account);
        $discoverRepositoryQuery->setShowDeleted(false);
        $discoverRepositoryQuery->setShowCancelled(false);
        $discoverRepositoryQuery->setLimit(3);
        $discoverEventOccurrences = $discoverRepositoryQuery->getEventOccurrences();

        return $this->render('account/manage/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'eventOccurrences' => $eventOccurrences,
            'discoverEventOccurrences' => $discoverEventOccurrences,
        ]));
    }
}
