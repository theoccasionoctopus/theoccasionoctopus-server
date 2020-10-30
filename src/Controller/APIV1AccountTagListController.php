<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\Tag;
use App\RepositoryQuery\TagRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;
use stdClass;

class APIV1AccountTagListController extends APIV1AccountController {


    public function listJSON($account_id, Request $request)
    {

        $this->buildAccount($account_id, $request);

        $out = array(
            'tags'=>array(),
        );

        $repositoryQuery = new TagRepositoryQuery($this->getDoctrine(), $this->account);
        if (!$this->account_permission_read_private) {
            $repositoryQuery->setPublicOnly();
        }
        $tags = $repositoryQuery->getTags();

        /** @var Tag $tag */
        foreach($tags as $tag) {
            $out['tags'][] = array(
                'id'=> $tag->getId(),
                'title'=>$tag->getTitle(),
                'description'=>$tag->getDescription(),
                'privacy'=>($tag->getPrivacy() == 0 ? 'public' : 'private'),
                'extra_fields'=>($tag->getExtraFields() ? $tag->getExtraFields() : new stdClass()),
            );
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );


    }


}
