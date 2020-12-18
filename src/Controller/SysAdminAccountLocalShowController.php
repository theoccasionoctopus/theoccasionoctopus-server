<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SysAdminAccountLocalShowController extends SysAdminBaseController
{
    public function index(Request $request, $account_id)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);

        $account = $repository->findOneById($account_id);
        if (!$account) {
            throw new  NotFoundHttpException('Not found');
        }

        $accountLocal = $account->getAccountLocal();
        if (!$accountLocal) {
            throw new  NotFoundHttpException('Not found');
        }

        // TODO list users who can manage this account

        return $this->render('sysadmin/account/local/details/index.html.twig', $this->getTemplateVariables([
            'account'=>$account,
            'accountLocal'=>$accountLocal,
            'usersManage'=>$doctrine->getRepository(User::class)->findCanManageAccount($account),
        ]));
    }
}
