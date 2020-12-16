<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\Tag;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\Library;
use App\RepositoryQuery\TagRepositoryQuery;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class APIV1AccountTagNewController extends APIV1AccountController {




    public function newJSON($account_id, Request $request,  HistoryWorkerService $historyWorkerService) {
        $this->buildAccount($account_id, $request);

        if (!$this->account_permission_write) {
            throw new AccessDeniedHttpException('This Token Can Not Write');
        }


        $existingTag = $this->getDoctrine()->getRepository(Tag::class)->findOneBy(array('account' => $this->account, 'title' => $request->get('title')));;
        if ($existingTag) {
            return new Response(
                json_encode([
                    'error' => [
                        'id' => 'title_already_exists',
                        'existing_tag_id' => $existingTag->getId(),
                    ],
                ]),
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }

        $tag = new Tag();
        $tag->setAccount($this->account);
        $tag->setId(Library::GUID());
        $tag->setPrivacy($this->account->getAccountLocal()->getDefaultPrivacy());
        $tag->setEnabled(True);

        // TODO If don't pass a title, should error nicely. There should at least be a title.
        if ($request->get('title')) {
            $tag->setTitle($request->get('title'));
        }
        if ($request->get('description')) {
            $tag->setDescription($request->get('description'));
        }

        $count = 0;
        while($request->request->get('extra_field_'.$count.'_name')) {
            $tag->setExtraField($request->request->get('extra_field_'.$count.'_name'), $request->request->get('extra_field_'.$count.'_value'));
            $count++;
        }

        $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->accessToken->getUser());
        $historyWorker->addTag($tag);
        $historyWorkerService->persistHistoryWorker($historyWorker);

        $out = [
            'tag' => [
                'id' => $tag->getId(),
            ]
        ];

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );


    }

}
