<?php

namespace App\Controller;

use App\Service\ActivityPubData\ActivityPubDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Note;

class AccountIdPublicNoteDetailsController extends AccountIdPublicController
{
    protected $note;

    protected function setUpAccountByIdPublicNote($account_id, $note_id, Request $request)
    {
        $this->setUpAccountByIdPublic($account_id, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Note::class);
        /** @var Note $note */
        $this->note = $repository->findOneBy(array('account'=>$this->account, 'id'=>$note_id));
        if (!$this->note) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function showNote($account_id, $note_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        $this->setUpAccountByIdPublicNote($account_id, $note_id, $request);

        # TODO If HTML viewer, redirect to some kind of HTML page?
        #if ($this->isRequestForActivityPubJSON($request)) {
        return new Response(
            json_encode($activityPubDataService->generateNoteObject($this->note), JSON_PRETTY_PRINT),
            Response::HTTP_OK,
            ['content-type' => 'application/activity+json']
        );
        #} else {
        #    return $this->redirectToRoute('account_public_note_show_note', ['account_username' => $this->account->getUsername(),'note_id' => $this->note->getId() ]);
        #}
    }

    public function create($account_id, $note_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        throw new  NotFoundHttpException('Not found');
    }
}
