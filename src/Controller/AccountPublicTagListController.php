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

class AccountPublicTagListController extends AccountPublicController
{
    public function index($account_username, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);

        $repositoryQuery = new TagRepositoryQuery($this->getDoctrine(), $this->account);
        $repositoryQuery->setPublicOnly();
        $tags = $repositoryQuery->getTags();


        return $this->render('account/public/tag/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'tags' => $tags,
        ]));
    }
}
