<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use App\RepositoryQuery\TagRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicTagDetailsController extends AccountPublicController
{
    public function showTag($account_username, $tag_id, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);


        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Tag::class);
        /** @var Tag $tag */
        $tag = $repository->findOneBy(array('account'=>$this->account, 'id'=>$tag_id));
        if (!$tag) {
            throw new  NotFoundHttpException('Not found');
        }
        if ($tag->getPrivacy() > 0) {
            throw new  NotFoundHttpException('Not found');
        }


        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setFromNow();
        $repositoryQuery->setTag($tag);
        $repositoryQuery->setPublicOnly();
        $events = $repositoryQuery->getEvents();




        return $this->render('account/public/tag/details/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'tag' => $tag,
            'events' => $events,
        ]));
    }
}
