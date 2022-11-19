<?php

namespace App\Controller;

use App\Entity\Nota;
use App\Entity\Tag;
use App\Repository\NotaRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractFOSRestController
{

    /**
     * @Rest\Get("/nota", name="nota_listar")
     */
    public function listarAction(NotaRepository $notaRepository): Response
    {
        $notas = $notaRepository->findAll();
        return $this->json($notas, 200, ["Content-Type" => "application/json"]);
    }

    /**
     * @Rest\Post("/nota/crear", name="nota_crear")
     */
    public function crearAction(Request $request, TagRepository $tagRepository, UserRepository $userRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $nota = new Nota();
        $titulo  = $request->get('titulo');
        $descripcion  = $request->get('descripcion');
        $tituloTags  = $request->get('tags');
        $usuarioEmail  = $request->get('usuario');

        if ($request->get('publica') == 1) {
            $publica  = true;
        } else {
            $publica  = false;
        }

        foreach ($tituloTags as $tituloTag) {
            $tag = $tagRepository->findOneByTitulo($tituloTag);
            if (is_null($tag)) {
                // Si el tag no existe lo creamos y 
                //luego lo añadimos al array de tags de la nueva nota
                $tag = new Tag();
                $tag->setTitulo($tituloTag);
                $em->persist($tag);

                $nota->addTag($tag);
            } else {
                // Si el tag ya existe lo añadimos al array de tags de la nueva nota
                $nota->addTag($tag);
            }
        }

        // ASUMO Q EL EMAIL DEL USUARIO Q VIENE EN LA PETICION
        // YA SE ENCUENTRA INSERTADO EN LA BD
        $usuario = $userRepository->findOneByEmail($usuarioEmail);
        $nota->setUsuario($usuario);
        $nota->setTitulo($titulo);
        $nota->setDescripcion($descripcion);
        $nota->setPublica($publica);
        $nota->setEliminada(false);
        $nota->setFechaEliminada(null);

        $em->persist($nota);
        $em->flush();

        return new JsonResponse('Se ha creado la nota satisfactoriamente', 201);
    }
}
