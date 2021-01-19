<?php

namespace App\Controller;

use App\Entity\History;
use App\RepositoryQuery\TagRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Tag;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;

class AccountManageHistoryListController extends AccountManageController
{
    public function index($account_username, Request $request)
    {
        $this->setUpAccountManage($account_username, $request);

        $doctrine = $this->getDoctrine();
        $histories = $doctrine->getRepository(History::class)->findBy(['account'=>$this->account], ['created'=>'DESC'], 100);

        return $this->render('account/manage/history/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'histories' => $histories,
        ]));
    }
}
