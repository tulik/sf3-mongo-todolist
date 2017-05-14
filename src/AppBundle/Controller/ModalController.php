<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\TaskGeneratorType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Document\Task;
use Faker;

class ModalController extends Controller
{
    public function footerModalAction()
    {
        return $this->render('AppBundle:modal:footer_modal.html.twig');
    }

    public function generateTasksModalAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $form = $this->createForm(TaskGeneratorType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $count = $formData['count'];
            $user = $this->getUser();

            if ($count >= 51) {
                $count = 50;
            }
            $tasksRepository = $dm->getRepository('AppBundle:Task');
            $userTask = $tasksRepository->getLastTask($user);
            if (is_null($userTask)) {
                $userTaskId = 0;
            } else {
                $userTaskId = $userTask->getItemId();
            }
            while ($count--) {
                $faker = Faker\Factory::create();
                $task = new Task();
                $task->setUserId($user);
                $task->setItemId(++$userTaskId);
                $task->setScheduled(new \DateTime(date('Y-m-d H:i:s', strtotime('+'.mt_rand(0, 30).' days '.mt_rand(0, 24). ' hours '.mt_rand(0, 60).' minutes'))));
                $task->setTimestamp(new \DateTime());
                $task->setValue($faker->sentence($nbWords = 6, $variableNbWords = true));
                $task->setCompletion(new \DateTime());
                $task->setDone(false);
                $dm->persist($task);
            }
            $dm->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('AppBundle:modal:generate_tasks.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
