<?php

namespace App\Controller;

use App\Constants;
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
    protected $tag;

    protected function setUpAccountPublicTag($account_username, $tag_id, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Tag::class);
        /** @var Tag $tag */
        $this->tag = $repository->findOneBy(array('account'=>$this->account, 'id'=>$tag_id));
        if (!$this->tag) {
            throw new  NotFoundHttpException('Not found');
        }
        if (
            ($this->tag->getPrivacy() == Constants::PRIVACY_LEVEL_PUBLIC) ||
            ($this->tag->getPrivacy() == Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS && $this->account_permission_read_only_followers)
        ) {
            // Great
        } else {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function showTag($account_username, $tag_id, Request $request)
    {
        $this->setUpAccountPublicTag($account_username, $tag_id, $request);

        // TODO should show occurrences, not just events!
        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setFromNow();
        $repositoryQuery->setTag($this->tag);
        if ($this->account_permission_read_only_followers) {
            $repositoryQuery->setPrivacyLevelOnlyFollowers();
        } else {
            $repositoryQuery->setPublicOnly();
        }
        $events = $repositoryQuery->getEvents();




        return $this->render('account/public/tag/details/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'tag' => $this->tag,
            'events' => $events,
        ]));
    }
}
