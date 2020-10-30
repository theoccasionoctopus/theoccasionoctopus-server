<?php

namespace App\Controller;

use App\Entity\Import;
use App\FilterParams\EventListFilterParams;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;



class AccountManageSettingsController extends AccountManageController
{

    public function index($account_username, Request $request)
    {

        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Import::class);
        $imports = $repository->findBy(['account'=>$this->account]);

        return $this->render('account/manage/settings/index.html.twig', $this->getTemplateVariables([
            'imports'=>$imports,
        ]));

    }


}
