<?php
namespace App\Command;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\UserManageAccount;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

class RemoveUserManagesAccountCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:remove-user-manages-account';

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
            ->setDescription('Remove User Manages Account')
            ->setHelp('Remove User Manages Account')
            ->addArgument('userid', InputArgument::REQUIRED, 'User Id (a number)')
            ->addArgument('accountid', InputArgument::REQUIRED, 'Account Id (a GUID string)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');

        $user = $doctrine->getRepository(User::class)->findOneById($input->getArgument('userid'));
        if (!$user) {
            $output->writeln('Can not find user');
            return 1;
        }

        $account = $doctrine->getRepository(Account::class)->findOneById($input->getArgument('accountid'));
        if (!$account) {
            $output->writeln('Can not find account');
            return 1;
        }

        $uma = $doctrine->getRepository(UserManageAccount::class)->findOneBy(['account'=>$account, 'user'=>$user]);
        if (!$uma) {
            $output->writeln('This user does not manage this account');
            return 1;
        }

        $doctrine->getManager()->remove($uma);
        $doctrine->getManager()->flush();

        $this->logger->info('Remove User Manages Account', ['user_id'=>$user->getId(), 'account_id'=>$account->getId()]);
        $output->writeln('Done');
        return 0;
    }
}
