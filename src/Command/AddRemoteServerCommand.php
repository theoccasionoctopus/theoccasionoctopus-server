<?php
namespace App\Command;


use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\RemoteServer;
use App\Entity\User;
use App\Service\RemoteServer\RemoteServerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class AddRemoteServerCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:add-remote-server';

    /** @var  RemoteServerService */
    protected $remoteServerService;

    public function __construct(RemoteServerService $remoteServerService)
    {
        parent::__construct();
        $this->remoteServerService = $remoteServerService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add Remote Server')
            ->setHelp('Add Remote Server')
            ->addArgument('url', InputArgument::REQUIRED, 'The URL')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $url = $input->getArgument('url');

        $remoteServer = $this->remoteServerService->add($url);
        $output->writeln('Id='. $remoteServer->getId());
        return 0;

    }

}
