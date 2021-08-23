<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\User;
use App\Entity\UserManageAccount;
use App\Exception\AccessDeniedRedirectToPublicURLIfPossibleException;
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

    /**
     *
     * If AccessDeniedRedirectToPublicURLIfPossibleException is thrown, $this->account should still be set and usable.
     * That is so that calling code can use that account to generate a suitable public URL to redirect to.
     */
    protected function setUpAccountManage($account_username, Request $request)
    {
        // Load account
        $doctrine = $this->getDoctrine();
        $accountLocal = $doctrine->getRepository(AccountLocal::class)->findOneByUsernameCanonical(Library::makeAccountUsernameCanonical($account_username));
        if (!$accountLocal || $accountLocal->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->account = $accountLocal->getAccount();

        // Must be a user
        // (We check this after loading the account specifically so that $this->account is still set)
        $user = $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
        if (!($user instanceof User)) {
            throw new AccessDeniedRedirectToPublicURLIfPossibleException();
        }

        // Check user has access
        $userManagesAccount = $doctrine->getRepository(UserManageAccount::class)->findOneBy(['user'=>$user,'account'=>$this->account]);
        if (!$userManagesAccount) {
            throw new AccessDeniedRedirectToPublicURLIfPossibleException();
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
        try {
            $this->setUpAccountManage($account_username, $request);
        } catch (AccessDeniedRedirectToPublicURLIfPossibleException $e) {
            return $this->redirectToRoute('account_public', ['account_username' => $this->account->getUsername() ]);
        }

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setFromNow();
        $repositoryQuery->setShowDeleted(false);
        $repositoryQuery->setShowCancelled(false);
        $repositoryQuery->setLimit(3);
        $eventOccurrences = $repositoryQuery->getEventOccurrences();

        $discoverRepositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $discoverRepositoryQuery->setAccountDiscoverEvents($this->account);
        $discoverRepositoryQuery->setPrivacyLevelOnlyFollowers();
        $discoverRepositoryQuery->setFromNow();
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
