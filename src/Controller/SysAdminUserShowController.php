<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SysAdminUserShowController extends SysAdminBaseController
{
    public function index(Request $request, $user_id)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $userRepository = $doctrine->getRepository(User::class);

        $user = $userRepository->findOneById($user_id);

        if (!$user) {
            throw new  NotFoundHttpException('Not found');
        }

        $accountRepository = $doctrine->getRepository(Account::class);

        return $this->render('sysadmin/user/details/index.html.twig', $this->getTemplateVariables([
            'user'=>$user,
            'accounts_this_user_can_manage'=>$accountRepository->findUserCanManage($user),
        ]));
    }
}
