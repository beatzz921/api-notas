<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @Route("/tag", name="app_tag", methods={"GET"})
     */
    public function index(TagRepository $tagRepository): Response
    {
        $titulo = "Tags Registradas";
        $tags = $tagRepository->findAll();

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
            'titulo' => $titulo
        ]);
    }

     /**
     * @Route("/tag/crear", name="app_tag_crear", methods={"GET", "POST"})
     */
    public function crear(Request $request): Response
    {
        $titulo = 'Nueva Tag';
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() and $form->isValid()) {
            $tag = $form->getData();
            $em->persist($tag);
            $em->flush();
            return $this->redirectToRoute('app_tag');
        }
        return $this->render('tag/form.html.twig', [
            'titulo' => $titulo,
            'form' => $form->createView()
        ]);
    }

}
