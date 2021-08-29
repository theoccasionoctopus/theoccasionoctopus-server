<?php

namespace App\MessageHandler;

use App\Entity\Import;
use App\Entity\User;
use App\Service\Import\ImportService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\NewUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewUserMessageHandler implements MessageHandlerInterface
{


    /** @var  ContainerInterface */
    protected $container;


    /** @var  \Swift_Mailer */
    protected $mailer;

    private $twig;

    /** @var LoggerInterface  */
    protected $logger;



    /** @var  EntityManagerInterface */
    protected $entityManager;

    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager, \Swift_Mailer $mailer, Environment $twig, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function __invoke(NewUserMessage $message)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $message->getUserId()]);
        if (!$user) {
            throw new \Exception('No User Found');
        }

        $parameters = [
          'user' => $user,
        ];

        $message = new \Swift_Message(
            $this->twig->render('email/new_user/subject.text.twig', $parameters)
        );

        $message->setFrom(
            [$this->container->getParameter('app.mailer_from_email') => $this->container->getParameter('app.instance_name')]
        )
        ->setTo($user->getEmail())
        ->setBody(
            $this->twig->render('email/new_user/body.html.twig', $parameters),
            'text/html'
        )
        ->addPart(
            $this->twig->render('email/new_user/body.text.twig', $parameters),
            'text/plain'
        );

        $this->mailer->send($message);
        $this->logger->info(
            'Email sent for new user',
            [
                'user_id' => $user->getId(),
                'email_sent_to' => $user->getEmail()
            ]
        );
    }
}
