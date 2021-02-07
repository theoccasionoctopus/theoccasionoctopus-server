<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\InboxSubmission;
use App\Entity\RemoteServerSendData;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SysAdminAccountLocalShowController extends SysAdminBaseController
{
    protected $account;

    protected $accountLocal;

    public function setUpSysadminAccountLocal(Request $request, $account_id)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);

        $this->account = $repository->findOneById($account_id);
        if (!$this->account) {
            throw new  NotFoundHttpException('Not found');
        }

        $this->accountLocal = $this->account->getAccountLocal();
        if (!$this->accountLocal) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function index(Request $request, $account_id)
    {
        $doctrine = $this->getDoctrine();

        $this->setUpSysadminAccountLocal($request, $account_id);

        return $this->render('sysadmin/account/local/details/index.html.twig', $this->getTemplateVariables([
            'account'=>$this->account,
            'accountLocal'=>$this->accountLocal,
            'usersManage'=>$doctrine->getRepository(User::class)->findCanManageAccount($this->account),
        ]));
    }


    public function apSent(Request $request, $account_id)
    {
        $doctrine = $this->getDoctrine();

        $this->setUpSysadminAccountLocal($request, $account_id);

        return $this->render('sysadmin/account/local/details/apSent.html.twig', $this->getTemplateVariables([
            'account'=>$this->account,
            'accountLocal'=>$this->accountLocal,
            'messages'=>$doctrine->getRepository(RemoteServerSendData::class)->getLatestForAccountLocal($this->accountLocal),
        ]));
    }


    public function apReceived(Request $request, $account_id)
    {
        $doctrine = $this->getDoctrine();

        $this->setUpSysadminAccountLocal($request, $account_id);

        return $this->render('sysadmin/account/local/details/apReceived.html.twig', $this->getTemplateVariables([
            'account'=>$this->account,
            'accountLocal'=>$this->accountLocal,
            'messages'=>$doctrine->getRepository(InboxSubmission::class)->getLatestForAccountLocal($this->accountLocal),
        ]));
    }
}
