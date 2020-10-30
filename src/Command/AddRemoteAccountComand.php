<?php
namespace App\Command;


use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\RemoteServer;
use App\Entity\User;
use App\Service\RemoteAccount\RemoteAccountService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class AddRemoteAccountComand extends Command
{
    protected static $defaultName = 'theocasionoctupus:add-remote-account';

    /** @var  ContainerInterface */
    protected $container;

    /** @var  RemoteAccountService */
    protected $remoteAccountService;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, RemoteAccountService $remoteAccountService)
    {
        parent::__construct();
        $this->container = $container;
        $this->remoteAccountService = $remoteAccountService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add Remote Account')
            ->setHelp('Add Remote Account')
            ->addArgument('server_id', InputArgument::REQUIRED, 'The Remote Server id')
            ->addArgument('username', InputArgument::REQUIRED, 'The Username of the account')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->container->get('doctrine');
        $remoteServer = $doctrine->getRepository(RemoteServer::class)->findOneById($input->getArgument('server_id'));
        if (!$remoteServer) {
            throw new \Exception('No Remote Server');
        }

        $username = $input->getArgument('username');

        $account = $this->remoteAccountService->add($remoteServer, $username);

        $output->writeln('Id='. $account->getId());
        return 0;
    }

}
