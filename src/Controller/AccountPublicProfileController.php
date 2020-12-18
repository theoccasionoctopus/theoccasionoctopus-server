<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Library;
use App\RepositoryQuery\TagRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicProfileController extends AccountPublicController
{
    public function index($account_username, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);


        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);
        $accounts_following = $repository->findFollowing($this->account);


        return $this->render('account/public/profile/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'accounts_following'=>$accounts_following,
        ]));
    }
}
