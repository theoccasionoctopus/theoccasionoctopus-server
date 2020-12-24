<?php

namespace App\Controller;

use App\Message\NewFollowRemoteAccountMessage;
use App\Service\Account\AccountService;
use App\Service\AccountRemote\AccountRemoteService;
use App\Service\RemoteServer\RemoteServerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\AccountFollowsAccount;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountManageProfileController extends AccountManageController
{
    public function indexManageProfile($account_username, Request $request, AccountService $accountService)
    {
        $this->build($account_username);

        $doctrine = $this->getDoctrine();

        if ($request->request->get('action') == 'unfollow') {

            # TODO CSFR
            $account = $doctrine->getRepository(Account::class)->findOneBy(['id'=>$request->request->get('guid')]);
            if ($account) {
                $accountService->unfollow($this->account, $account);
                return $this->redirectToRoute('account_manage_profile', ['account_username' => $this->account->getUsername()]);
            }
        }

        $accounts_following = $doctrine->getRepository(Account::class)->findFollowing($this->account);
        $accounts_followers = $doctrine->getRepository(Account::class)->findFollowers($this->account);

        return $this->render('account/manage/profile/index.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'accounts_following' => $accounts_following,
            'accounts_followers' => $accounts_followers,
        ]));
    }

    public function indexNewFollowLocal($account_username, Request $request, AccountService $accountService)
    {
        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);

        if ($request->request->get('action') == 'follow') {


            # TODO CSFR
            $account_to_follow = $repository->findOneBy(array('id' => $request->request->get('guid')));
            if ($account_to_follow) {
                $accountService->follow($this->account, $account_to_follow);
                return $this->redirectToRoute('account_manage_profile', ['account_username' => $this->account->getUsername()]);
            }
        }

        $accounts_to_follow = $repository->findAllLocalToFollow($this->account);

        return $this->render('account/manage/profile/new_follow_local.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'accounts_to_follow' => $accounts_to_follow,
        ]));
    }

    public function indexNewFollowRemote($account_username, Request $request, RemoteServerService $remoteServerService, AccountRemoteService $remoteAccountService, AccountService $accountService)
    {
        if (!$this->getParameter('app.instance_federation')) {
            return new Response(
                json_encode(['error'=>'federation_off']),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['content-type' => 'application/json']
            );
        }
        $this->build($account_username);

        $doctrine = $this->getDoctrine();

        if ($request->request->get('action') == 'follow') {
            list($username, $hostname) = Library::parseAccountHandleWithServerToUsernameAndHost($request->request->get('username'));

            // TODO catch errors from here and show to user nicely

            // TODO This will save remote server even if remote user then errors - can we make it so remoteServer only saved if user is?

            $remoteServer = $remoteServerService->addByHostName($hostname);

            $remoteAccount = $remoteAccountService->addByUsername($remoteServer, $username);

            // Save
            $accountService->follow($this->account, $remoteAccount);

            // Return
            return $this->redirectToRoute('account_manage_profile', ['account_username' => $this->account->getUsername()]);
        }

        return $this->render('account/manage/profile/new_follow_remote.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
        ]));
    }
}
