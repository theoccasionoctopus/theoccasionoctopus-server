<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\UserManageAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;

class APIActivityStreamsController extends BaseController
{

    /** @var  Account */
    protected $account;

    protected function buildAccount($account_id, Request $request)
    {

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);
        $this->account = $repository->findOneById($account_id);
        if (!$this->account) {
            throw new  NotFoundHttpException('Not found');
        }
        if (!$this->account->getAccountLocal()) {
            // API should only be used on local accounts
            throw new  NotFoundHttpException('Not found');
        }

    }


    public function index($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);
        return $this->getResponseAccountActivityStreamsProfileJSON($this->account);
    }

    public function inbox($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);


        // TODO

    }

    public function outbox($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        // TODO

    }

}