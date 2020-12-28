<?php

namespace App\Controller;

use App\Constants;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\RepositoryQuery\TagRepositoryQuery;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Response;

class APIV1AccountTagDetailsController extends APIV1AccountController
{


    /** @var  Tag */
    protected $tag;

    protected function buildTag($account_id, $tag_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Tag::class);

        $this->tag = $repository->findOneBy(array('account'=>$this->account, 'id'=>$tag_id));
        if (!$this->tag) {
            throw new  NotFoundHttpException('Not found');
        }
        if (
            $this->tag->getPrivacy() == Constants::PRIVACY_LEVEL_PUBLIC ||
            ($this->account_permission_read_private && $this->tag->getPrivacy() == Constants::PRIVACY_LEVEL_PRIVATE) ||
            ($this->account_permission_read_only_followers && $this->tag->getPrivacy() == Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS)
        ) {
            // Great!
        } else {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function showJSON($account_id, $tag_id, Request $request)
    {
        $this->buildTag($account_id, $tag_id, $request);

        $out['tag'] = array(
            'id'=> $this->tag->getId(),
            'title'=>$this->tag->getTitle(),
            'description'=>$this->tag->getDescription(),
            'privacy'=>$this->privacyLevelToAPIString($this->tag->getPrivacy()),
            'extra_fields'=>($this->tag->getExtraFields() ? $this->tag->getExtraFields() : new stdClass()),
        );

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function editJSON($account_id, $tag_id, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildTag($account_id, $tag_id, $request);

        if (!$this->account_permission_write) {
            throw new AccessDeniedHttpException('This Token Can Not Write');
        }

        ############# Set Changes!

        $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->accessToken->getUser());
        $changedTag = false;

        if ($request->get('title') && $request->get('title') != $this->tag->getTitle()) {
            $this->tag->setTitle($request->get('title'));
            $changedTag = true;
        }

        if ($request->get('description') && $request->get('description') != $this->tag->getDescription()) {
            $this->tag->setDescription($request->get('description'));
            $changedTag = true;
        }

        $count = 0;
        while ($request->get('extra_field_'.$count.'_name')) {
            if ($this->tag->getExtraField($request->get('extra_field_'.$count.'_name')) != $request->get('extra_field_'.$count.'_value')) {
                $this->tag->setExtraField($request->get('extra_field_' . $count . '_name'), $request->get('extra_field_' . $count . '_value'));
                $changedTag = true;
            }
            $count++;
        }


        ########### Result and Save!
        $out = [
            'tag' => [
                'id' => $this->tag->getId(),
            ],
            'changes' => false,
        ];
        if ($changedTag) {
            $historyWorker->addTag($this->tag);
        }
        if ($historyWorker->hasContents()) {
            $historyWorkerService->persistHistoryWorker($historyWorker);
            $out['changes'] = true;
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
