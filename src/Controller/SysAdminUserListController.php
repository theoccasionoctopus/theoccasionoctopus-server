<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SysAdminUserListController extends SysAdminBaseController
{
    public function index(Request $request)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(User::class);

        return $this->render('sysadmin/user/index.html.twig', $this->getTemplateVariables([
            'users'=>$repository->findAll(),
        ]));
    }
}
