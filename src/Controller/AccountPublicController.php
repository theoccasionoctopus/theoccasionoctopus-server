<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\Tag;
use App\Entity\User;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use App\Service\ActivityPubData\ActivityPubDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicController extends BaseController
{

    /** @var  Account */
    protected $account;

    protected $account_permission_read_only_followers = false;


    protected function setUpAccountPublic($account_username, Request $request)
    {
        $this->setUp($request);
        $doctrine = $this->getDoctrine();
        // Load Account
        $accountLocal = $doctrine->getRepository(AccountLocal::class)->findOneByUsernameCanonical(Library::makeAccountUsernameCanonical($account_username));
        if (!$accountLocal || $accountLocal->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->account = $accountLocal->getAccount();
        // If user is logged in, do they have special read permissions here?
        $user = $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
        if ($user && $user instanceof User) {
            if ($doctrine->getRepository(Account::class)->findAccountsManagedByUserThatFollowsThisAccount($user, $this->account)) {
                $this->account_permission_read_only_followers = true;
            }
        }
    }


    public function indexAccount($account_username, Request $request, ActivityPubDataService $activityPubDataService)
    {
        $this->setUpAccountPublic($account_username, $request);

        if ($this->isRequestForActivityPubJSON($request)) {
            return new Response(
                json_encode($activityPubDataService->generateActorForAccount($this->account), JSON_PRETTY_PRINT),
                Response::HTTP_OK,
                ['content-type' => 'application/activity+json']
            );
        }

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        if ($this->account_permission_read_only_followers) {
            $repositoryQuery->setPrivacyLevelOnlyFollowers();
        } else {
            $repositoryQuery->setPublicOnly();
        }
        $repositoryQuery->setFromNow();
        $repositoryQuery->setShowDeleted(false);
        $repositoryQuery->setShowCancelled(false);
        $repositoryQuery->setLimit(3);

        $eventOccurrences = $repositoryQuery->getEventOccurrences();

        return $this->render('account/public/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'eventOccurrences' => $eventOccurrences,
        ]));
    }
}
