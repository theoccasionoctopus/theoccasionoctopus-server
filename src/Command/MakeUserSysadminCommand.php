<?php
namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

class MakeUserSysadminCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:make-user-sysadmin';

    /** @var  ContainerInterface */
    protected $container;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        parent::__construct();
        $this->container = $container;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Make User Sysadmin')
            ->setHelp('Make User Sysadmin')
            ->addArgument('email', InputArgument::REQUIRED, 'The user email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        $accountRepository = $doctrine->getRepository(User::class);

        $user = $accountRepository->findOneByEmail($input->getArgument('email'));

        if (!$user) {
            throw new \Exception('Account Not Found');
        }

        $user->addRole('ROLE_SYSADMIN');

        $doctrine->getManager()->persist($user);
        $doctrine->getManager()->flush();
        $this->logger->info('User made sys admin', ['user_id'=>$user->getId()]);
        return 0;
    }
}
