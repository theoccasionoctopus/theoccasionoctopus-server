<?php

namespace App\Controller;

use App\RepositoryQuery\TagRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Tag;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;

class AccountManageTagListController extends AccountManageController
{
    public function indexManageTag($account_username, Request $request)
    {
        $this->build($account_username);

        $repositoryQuery = new TagRepositoryQuery($this->getDoctrine(), $this->account);
        $tags = $repositoryQuery->getTags();

        return $this->render('account/manage/tag/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'tags' => $tags,
        ]));
    }
}
