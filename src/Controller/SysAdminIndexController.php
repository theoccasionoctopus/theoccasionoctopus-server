<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SysAdminIndexController extends SysAdminBaseController
{
    public function index(Request $request)
    {

        return $this->render('sysadmin/index.html.twig', $this->getTemplateVariables());

    }

}
