<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\Tag;
use App\Entity\User;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if ($user && $user instanceof User) {
            if ($doctrine->getRepository(Account::class)->findAccountsManagedByUserThatFollowsThisAccount($user, $this->account)) {
                $this->account_permission_read_only_followers = true;
            }
        }
    }


    public function indexAccount($account_username, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);

        if ($this->isRequestForAccountActivityStreamsProfileJSON($request)) {
            return $this->getResponseAccountActivityStreamsProfileJSON($this->account, $request);
        }

        return $this->render('account/public/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
        ]));
    }
}
