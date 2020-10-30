<?php

namespace App\Controller;

use App\Service\HistoryWorker\HistoryWorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Tag;
use App\Library;
use App\Form\TagNewType;
use Symfony\Component\HttpFoundation\Request;



class AccountManageTagNewController extends AccountManageController
{


    public function newTag($account_username,  Request $request,  HistoryWorkerService $historyWorkerService)
    {

        $this->build($account_username);

        // build the form
        $form = $this->createForm(TagNewType::class, null, array(
            'account' => $this->account,
        ) );

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            // Set data
            $tag = new Tag();
            $tag->setTitle($form->get('title')->getData());
            $tag->setDescription($form->get('description')->getData());
            $tag->setEnabled(true);
            $tag->setId(Library::GUID());
            $tag->setAccount($this->account);
            $tag->setPrivacy($form->get('privacy')->getData());

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addTag($tag);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            return $this->redirectToRoute('account_manage_tag', ['account_username'=>$this->account->getUsername()]);
        }

        return $this->render('account/manage/tag/new.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'form' => $form->createView(),
        ]));

    }


}
