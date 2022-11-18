<?php

namespace App\Controller;

use App\Entity\Nota;
use App\Form\NotaType;
use App\Repository\NotaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotaController extends AbstractController
{
    /**
     * @Route("/nota", name="app_nota", methods={"GET"})
     */
    public function index(NotaRepository $notaRepository): Response
    {
        $usuario = $this->getUser();
        $titulo = "Notas Registradas";
        $listarRegistradas = true;
        $notas = [];
        if (isset($_GET['filtro'])) {
            $filtro = $_GET['filtro'];
            $notas = $notaRepository->filtrar($filtro, false, $usuario);
        } else {
            $notas = $notaRepository->findBy(array('eliminada' => false, 'usuario' => $usuario));
        }

        return $this->render('nota/index.html.twig', [
            'notas' => $notas,
            'titulo' => $titulo,
            'listarRegistradas' => $listarRegistradas
        ]);
    }

    /**
     * @Route("/nota/crear", name="app_nota_crear", methods={"GET", "POST"})
     */
    public function crear(Request $request): Response
    {
        $titulo = 'Nueva Nota';
        $nota = new Nota();
        $form = $this->createForm(NotaType::class, $nota);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() and $form->isValid()) {
            $usuario = $this->getUser();
            $nota = $form->getData();
            $nota->setEliminada(false);
            $nota->setUsuario($usuario);
            $em->persist($nota);
            $em->flush();

            return $this->redirectToRoute('app_nota');
        }
        return $this->render('nota/form.html.twig', [
            'titulo' => $titulo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/nota/editar/{id}", name="app_nota_editar", methods={"GET", "POST"})
     */
    public function editar(Request $request, NotaRepository $notaRepository, $id): Response
    {
        $titulo = 'Editar Nota';
        $em = $this->getDoctrine()->getManager();

        $nota = $notaRepository->findOneById($id);
        $form = $this->createForm(NotaType::class, $nota);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $nota = $form->getData();
            $em->persist($nota);
            $em->flush();

            return $this->redirectToRoute('app_nota');
        }
        return $this->render('nota/form.html.twig', [
            'titulo' => $titulo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/nota/eliminar/{id}", name="app_nota_eliminar")
     */
    public function eliminar(NotaRepository $notaRepository, $id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $nota = $notaRepository->findOneById($id);
        $fecha = new \DateTime(null, new \DateTimeZone('America/Havana'));
        $nota->setEliminada(true);
        $nota->setfechaEliminada($fecha);
        $em->persist($nota);
        $em->flush();

        return $this->redirectToRoute('app_nota');
    }

    /**
     * @Route("/nota/eliminada", name="app_nota_eliminada", methods={"GET"})
     */
    public function eliminada(NotaRepository $notaRepository): Response
    {
        $usuario = $this->getUser();
        $titulo = "Notas Eliminadas";
        $listarRegistradas = false;
        if (isset($_GET['filtro'])) {
            $filtro = $_GET['filtro'];
            $notas = $notaRepository->filtrar($filtro, true, $usuario);
        } else {
            $notas = $notaRepository->findBy(array('eliminada' => true, 'usuario' => $usuario));
        }

        return $this->render('nota/index.html.twig', [
            'notas' => $notas,
            'titulo' => $titulo,
            'listarRegistradas' => $listarRegistradas

        ]);
    }

    /**
     * @Route("/nota/restaurar/{id}", name="app_nota_restaurar")
     */
    public function restaurar(NotaRepository $notaRepository, $id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $nota = $notaRepository->findOneById($id);
        $nota->setEliminada(false);
        $nota->setfechaEliminada(null);
        $em->persist($nota);
        $em->flush();

        return $this->redirectToRoute('app_nota_eliminada');
    }
}
