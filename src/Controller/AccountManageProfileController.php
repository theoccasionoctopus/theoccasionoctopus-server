<?php

namespace App\Controller;

use App\Service\RemoteAccount\RemoteAccountService;
use App\Service\RemoteServer\RemoteServerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\AccountFollowsAccount;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;

class AccountManageProfileController extends AccountManageController
{
    public function indexManageProfile($account_username, Request $request)
    {
        $this->build($account_username);


        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);
        $accounts_following = $repository->findFollowing($this->account);


        return $this->render('account/manage/profile/index.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'accounts_following' => $accounts_following,
        ]));
    }

    public function indexNewFollowLocal($account_username, Request $request)
    {
        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);

        if ($request->request->get('action') == 'follow') {


            # TODO CSFR
            $account_to_follow = $repository->findOneBy(array('id' => $request->request->get('guid')));
            if ($account_to_follow) {
                $this->startFollowingAccount($account_to_follow);
                return $this->redirectToRoute('account_manage_profile', ['account_username' => $this->account->getUsername()]);
            }
        }

        $accounts_to_follow = $repository->findAllLocalToFollow($this->account);

        return $this->render('account/manage/profile/new_follow_local.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'accounts_to_follow' => $accounts_to_follow,
        ]));
    }

    public function indexNewFollowRemote($account_username, Request $request, RemoteServerService $remoteServerService, RemoteAccountService $remoteAccountService)
    {
        $this->build($account_username);

        $doctrine = $this->getDoctrine();

        if ($request->request->get('action') == 'follow') {
            $username = $request->request->get('username');
            // TODO strip leading @'s

            list($username, $hostname) = explode('@', $username);

            // TODO catch errors from here and show to user nicely

            // TODO This will save remote server even if remote user then errors - can we make it so remoteServer only saved if user is?

            $remoteServer = $remoteServerService->addByHostName($hostname);

            $remoteAccount = $remoteAccountService->add($remoteServer, $username);

            $this->startFollowingAccount($remoteAccount);

            return $this->redirectToRoute('account_manage_profile', ['account_username' => $this->account->getUsername()]);
        }

        return $this->render('account/manage/profile/new_follow_remote.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
        ]));
    }

    protected function startFollowingAccount(Account $account)
    {
        if ($this->account == $account) {
            return;
        }
        $doctrine = $this->getDoctrine();
        $followsRepo = $doctrine->getRepository(AccountFollowsAccount::class);
        $account_follows_account = $followsRepo->findOneBy(array('account' => $this->account, 'followsAccount' => $account));
        if (!$account_follows_account) {
            $account_follows_account = new AccountFollowsAccount();
            $account_follows_account->setAccount($this->account);
            $account_follows_account->setFollowsAccount($account);
        }
        $account_follows_account->setFollows(true);
        $doctrine->getManager()->persist($account_follows_account);
        $doctrine->getManager()->flush();
    }
}
