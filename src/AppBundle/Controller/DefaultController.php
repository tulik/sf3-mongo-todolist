<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Document\Task;
use AppBundle\Form\TaskType;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $tasksRepository = $dm->getRepository('AppBundle:Task');
        $tasks = $tasksRepository->findBy(['userId' => $user->getUsername()]);
        $tasksRepository = $dm->getRepository('AppBundle:Task');
        $userTask = $tasksRepository->getLastTask($user);
        if (is_null($userTask)) {
            $userTaskId = 0;
        } else {
            $userTaskId = $userTask->getItemId();
        }

        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setUserId($this->getUser());
            $task->setItemId(++$userTaskId);
            $task->setScheduled(new \DateTime(date('Y-m-d H:i:s', strtotime($task->getScheduled()))));
            $task->setCompletion(new \DateTime());
            $task->setTimestamp(new \DateTime());
            $task->setDone(false);
            $dm->persist($task);
            $dm->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('AppBundle:default:index.html.twig', [
            'form' => $form->createView(),
            'tasks' => $tasks,
        ]);
    }

    public function markAsCompletedAction($task)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $task = $dm->find('AppBundle:Task', $task);
        $task->setCompletion(new \DateTime());
        $task->setDone(true);
        $dm->persist($task);
        $dm->flush();

        return $this->redirectToRoute('homepage');
    }

    public function deleteItemAction($task)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $task = $dm->find('AppBundle:Task', $task);
        $dm->remove($task);
        $dm->flush();

        return $this->redirectToRoute('homepage');
    }

    public function showCredentialsAction()
    {
        $em = $this->get('doctrine_mongodb')->getManager();
        $userRepository = $em->getRepository('AppBundle:User');
        $user = $userRepository->getRandomUser();

        return $this->render('AppBundle:partials:show_credentials.html.twig', [
            'username' => $user->getUsername(),
            'password' => $user->getUsername(),
        ]);
    }

    public function aboutAction()
    {
        return $this->render('AppBundle:default:about.html.twig');
    }
}
