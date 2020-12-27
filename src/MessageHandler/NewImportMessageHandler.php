<?php

namespace App\MessageHandler;

use App\Entity\Import;
use App\Service\Import\ImportService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewImportMessage;
use Doctrine\ORM\EntityManagerInterface;

class NewImportMessageHandler implements MessageHandlerInterface
{


    /** @var ImportService */
    protected $importService;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ImportService $importService)
    {
        $this->entityManager = $entityManager;
        $this->importService = $importService;
    }

    public function __invoke(NewImportMessage $message)
    {
        $import = $this->entityManager->getRepository(Import::class)->findOneBy(['id'=>$message->getImportId()]);
        if (!$import) {
            throw new \Exception('No Import Found');
        }
        $this->importService->import($import);
    }
}
