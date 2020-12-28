<?php

namespace App\Controller;

use App\Form\TagEditType;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Tag;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;

class AccountManageTagDetailsController extends AccountManageController
{


    /** @var  Tag */
    protected $tag;

    protected function buildTag($account_username, $tag_id)
    {
        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Tag::class);

        $this->tag = $repository->findOneBy(array('account' => $this->account, 'id' => $tag_id));
        if (!$this->tag) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function indexShow($account_username, $tag_id, Request $request)
    {
        $this->buildTag($account_username, $tag_id);

        // TODO should show occurrences, not just events!
        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setFromNow();
        $repositoryQuery->setTag($this->tag);
        $events = $repositoryQuery->getEvents();


        return $this->render('account/manage/tag/details/index.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'tag' => $this->tag,
            'events' => $events,
        ]));
    }

    public function indexEditDetails($account_username, $tag_id, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildTag($account_username, $tag_id);

        // build the form
        $form = $this->createForm(TagEditType::class, $this->tag);

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addTag($this->tag);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Tag edited!'
            );
            return $this->redirectToRoute('account_manage_tag_show_tag', ['account_username' => $this->account->getUsername(),'tag_id' => $this->tag->getId() ]);
        }

        return $this->render('account/manage/tag/details/editDetails.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'tag' => $this->tag,
            'form' => $form->createView(),
        ]));
    }
}
