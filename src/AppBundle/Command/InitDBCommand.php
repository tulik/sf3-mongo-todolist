<?php

namespace AppBundle\Command;

use Faker;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use AppBundle\Document\Task;

class InitDBCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('init:db')
            ->setDescription('Initialize database')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Size of database.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createUsers($input->getOption('count'));
    }
    private function createUsers($count)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $userRepository = $dm->getRepository('AppBundle:User');
        $faker = Faker\Factory::create();

        while (--$count) {
            $username = $faker->userName;
            $random_position = rand(0, strlen($username)-1);
            $chars = "qwertyuiopasdfghjklzxcvbnm01234567890";
            $random_char = $chars[rand(0, strlen($chars)-1)];
            $username = substr($username, 0, $random_position).$random_char.substr($username, $random_position);
            $email = $random_char.$faker->email;
            $password = $username;
            $manipulator = $this->getContainer()->get('fos_user.util.user_manipulator');
            $manipulator->create($username, $password, $email, true, false);
            $manipulator->addRole($username, '{ROLE_USER}');
            for ($i = 1; $i <= 6; $i++) {
                if ($i%2 == 0) {
                    $this->addItem($userRepository->getUserByName($username), $i, true);
                } else {
                    $this->addItem($userRepository->getUserByName($username), $i, false);
                }
            }
        }
    }
    private function addItem($user, $taskId, $done)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $faker = Faker\Factory::create();
        $task = new Task();
        $task->setUserId($user);
        $task->setItemId($taskId);
        $task->setScheduled(new \DateTime(date('Y-m-d H:i:s', strtotime('+'.mt_rand(0, 30).' days '.mt_rand(0, 24). ' hours '.mt_rand(0, 60).' minutes'))));
        $task->setTimestamp(new \DateTime());
        $task->setValue($faker->sentence($nbWords = 6, $variableNbWords = true));
        $task->setCompletion(new \DateTime());
        $task->setDone($done);
        $dm->persist($task);
    }
}
