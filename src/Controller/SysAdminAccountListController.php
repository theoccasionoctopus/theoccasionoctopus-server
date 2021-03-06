<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SysAdminAccountListController extends SysAdminBaseController
{
    public function local(Request $request)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);

        return $this->render('sysadmin/account/local/index.html.twig', $this->getTemplateVariables([
            'accounts'=>$repository->findAllLocal(),
        ]));
    }

    public function remote(Request $request)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);

        return $this->render('sysadmin/account/remote/index.html.twig', $this->getTemplateVariables([
            'accounts'=>$repository->findAllRemote(),
        ]));
    }
}
