<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TodoController
 * todo_index
 * todo_add
 * todo_edit
 * todo_delete
 * todo_check
 * todo_uncheck.
 *
 * @Route("/todolist", name="todo_")
 * @package App\Controller
 */
class TodoController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $tasks = $this->getDoctrine()->getRepository(Task::class)->findAll();

        foreach ($tasks as $task) {
            $ponderators = $task->getPonderators();
            $priority = 0;

            foreach ($ponderators as $ponderator) {
                $priority += $ponderator->getCoefficient();
            }

            $task->setPriority($priority);
        }

        return $this->render('todo/index.html.twig', compact('tasks'));
    }

    /**
     * @Route("/add", name="add")
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function Add(Request $request)
    {
        // Prépare la création d'une nouvelle tâche
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        // On traite le formulaire s’il a été remplis
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($task);
            $manager->flush();
            $this->addFlash('info', 'Nouvelle tâche ajoutée');

            // Redirection
            return $this->redirectToRoute('todo_index');
        }

        // Envoie du formulaire à la vue
        return $this->render('todo/todo_add.html.twig', [
            'add_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit", requirements={"id":"\d+"})
     *
     * @param Task $task
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function Edit(Task $task, Request $request)
    {
        // Prépare le formulaire
        $form = $this->createForm(TaskType::class, $task);

        // On traite le formulaire s’il a été remplis
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire a été envoyé
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($task);
            $manager->flush();
            $this->addFlash('info', 'Commentaire modifié');

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/todo_edit.html.twig', [
            'edit_form' => $form->createView(),
        ]);
    }

    /**
     * @route("/delete/{id}", name="delete", requirements={"id":"\d+"})
     *
     * @param Task $task
     * @return RedirectResponse
     */
    public function delete(Task $task)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($task);
        $manager->flush();

        $this->addFlash('info', 'La tâche a bien été supprimé');

        return $this->redirectToRoute('todo_index');
    }

    /**
     * @route("/check/{id}", name="check", requirements={"id":"\d+"})
     *
     * @param Task $task
     * @return RedirectResponse
     */
    public function check(Task $task)
    {
        $task = $task->setChecked(false);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($task);
        $manager->flush();
        $this->addFlash('info', 'Commentaire coché');

        return $this->redirectToRoute('todo_index');
    }

    /**
     * @route("/uncheck/{id}", name="uncheck", requirements={"id":"\d+"})
     *
     * @param Task $task
     * @return RedirectResponse
     */
    public function unCheck(Task $task)
    {
        $task = $task->setChecked(true);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($task);
        $manager->flush();
        $this->addFlash('info', 'Commentaire dé-coché');

        return $this->redirectToRoute('todo_index');
    }

    // TODO : Faire un controller switch_check
}
