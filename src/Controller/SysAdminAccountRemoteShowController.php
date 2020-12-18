<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SysAdminAccountRemoteShowController extends SysAdminBaseController
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

        $accountRemote = $account->getAccountRemote();

        if (!$accountRemote) {
            throw new  NotFoundHttpException('Not found');
        }
        return $this->render('sysadmin/account/remote/details/index.html.twig', $this->getTemplateVariables([
            'account'=>$account,
            'accountRemote'=>$accountRemote,
        ]));
    }
}
