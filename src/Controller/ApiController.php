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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractFOSRestController
{

    /**
     * @Rest\Get("/nota/listar", name="nota_listar")
     * @OA\Response(
     *     response=200,
     *     description="Retorna un listado de las notas registradas",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Nota::class, groups={"full"}))
     *     )
     * )
     */
    public function listarAction(NotaRepository $notaRepository): Response
    {
        $notasSerializadas = [];
        $notas = $notaRepository->findBy(array('eliminada' => false));

        foreach ($notas as $nota) {
            $notasSerializadas[] = $nota->serializar();
        }

        return new JsonResponse($notasSerializadas);
    }


    /**
     * @Rest\Get("/nota/eliminadas", name="nota_eliminadas")
     * @OA\Response(
     *     response=200,
     *     description="Retorna un listado de las notas eliminadas",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Nota::class, groups={"full"}))
     *     )
     * )
     */
    public function eliminadasAction(NotaRepository $notaRepository): Response
    {
        $notasSerializadas = [];
        $notas = $notaRepository->findBy(array('eliminada' => true));

        foreach ($notas as $nota) {
            $notasSerializadas[] = $nota->serializar();
        }

        return new JsonResponse($notasSerializadas);
    }


    /**
     * @Rest\Post("/nota/crear", name="nota_crear")
     * @OA\Response(
     *     response=201,
     *     description="Crea una nueva nota",
     * )
     * @OA\Parameter(
     *     name="titulo",
     *     in="query",
     *     description="El titulo de la nota",
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="descripcion",
     *     in="query",
     *     description="La descripcion de la nota",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="tags",
     *     in="query",
     *     description="Los tags asociados a la nota",
     *     @OA\Schema(type="array")
     * )
     * 
     * @OA\Parameter(
     *     name="usuario",
     *     in="query",
     *     description="El email del usuario al que pertenecera la nota",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="publica",
     *     in="query",
     *     description="La visibilidad de la nota",
     *     @OA\Schema(type="integer")
     * )
     */
    public function crearAction(Request $request, TagRepository $tagRepository, UserRepository $userRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $nota = new Nota();
        if (
            is_null($request->get('titulo')) or
            is_null($request->get('descripcion')) or
            is_null($request->get('tags')) or
            is_null($request->get('publica')) or is_null($request->get('usuario'))
        ) {
            return new JsonResponse(['message' => 'Faltan parámetros en la petición', 'code' => 400, 'status' => 'error']);
        } else {
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

            return new JsonResponse(['data' => $nota->serializar(), 'message' => 'Se ha creado la nota satisfactoriamente', 'code' => 201, 'status' => 'success']);
        }
    }

    /**
     * @Rest\Post("/nota/editar", name="nota_editar")
     *
     * @OA\Response(
     *     response=200,
     *     description="Edita una nota",
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="El id de la nota",
     *     @OA\Schema(type="integer")
     * )
     * 
     * @OA\Parameter(
     *     name="titulo",
     *     in="query",
     *     description="El titulo de la nota",
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="descripcion",
     *     in="query",
     *     description="La descripcion de la nota",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="tags",
     *     in="query",
     *     description="Los tags asociados a la nota",
     *     @OA\Schema(type="array")
     * )
     * 
     * @OA\Parameter(
     *     name="publica",
     *     in="query",
     *     description="La visibilidad de la nota",
     *     @OA\Schema(type="integer")
     * )
     */
    public function editarAction(Request $request, NotaRepository $notaRepository, TagRepository $tagRepository): Response
    {
        $em = $this->getDoctrine()->getManager();

        $id  = $request->get('id');
        $nota = $notaRepository->findOneById($id);

        if (is_null($nota)) {
            return new JsonResponse(['message' => 'No se ha encontrado la nota', 'code' => 404, 'satus' => 'error']);
        } else {
            if (
                is_null($request->get('titulo')) or
                is_null($request->get('descripcion')) or
                is_null($request->get('tags')) or
                is_null($request->get('publica'))
            ) {
                return new JsonResponse(['message' => 'Faltan parámetros en la petición', 'code' => 400, 'satus' => 'error']);
            } else {
                $titulo  = $request->get('titulo');
                $descripcion  = $request->get('descripcion');
                $tituloTags  = $request->get('tags');

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

                $nota->setTitulo($titulo);
                $nota->setDescripcion($descripcion);
                $nota->setPublica($publica);

                $em->persist($nota);
                $em->flush();

                return new JsonResponse(['data' => $nota->serializar(), 'message' => 'Se ha editado la nota satisfactoriamente', 'code' => 200, 'satus' => 'success']);
            }
        }
    }

    /**
     * @Rest\Post("/nota/eliminar", name="nota_eliminar")
     * @OA\Response(
     *     response=200,
     *     description="Elimina una nota que ha sido registrada",
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="Id de la nota a eliminar",
     *     @OA\Schema(type="integer")
     * )
     */
    public function eliminarAction(Request $request, NotaRepository $notaRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        if (
            is_null($request->get('id'))
        ) {
            return new JsonResponse(['message' => 'Faltan parámetros en la petición', 'code' => 400, 'status' => 'error']);
        } else {
            $id  = $request->get('id');
            $nota = $notaRepository->findOneById($id);

            if (is_null($nota)) {
                return new JsonResponse(['message' => 'No se ha encontrado la nota', 'code' => 404, 'satus' => 'error']);
            } else {
                $nota->setEliminada(true);
                $fecha = new \DateTime(null, new \DateTimeZone('America/Havana'));
                $nota->setFechaEliminada($fecha);
                $em->persist($nota);
                $em->flush();

                return new JsonResponse(['message' => 'Se ha eliminado la nota satisfactoriamente', 'code' => 200, 'status' => 'success']);
            }
        }
    }

    /**
     * @Rest\Post("/nota/restaurar", name="nota_restaurar")
     * @OA\Response(
     *     response=200,
     *     description="Restaura una nota que ha sido eliminada",
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="Id de la nota a restaurar",
     *     @OA\Schema(type="integer")
     * )
     */
    public function restaurarAction(Request $request, NotaRepository $notaRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        if (
            is_null($request->get('id'))
        ) {
            return new JsonResponse(['message' => 'Faltan parámetros en la petición', 'code' => 400, 'status' => 'error']);
        } else {
            $id  = $request->get('id');
            $nota = $notaRepository->findOneById($id);

            if (is_null($nota)) {
                return new JsonResponse(['message' => 'No se ha encontrado la nota', 'code' => 404, 'satus' => 'error']);
            } else {
                $nota->setEliminada(false);
                $nota->setFechaEliminada(null);
                $em->persist($nota);
                $em->flush();

                return new JsonResponse(['message' => 'Se ha restaurado la nota satisfactoriamente', 'code' => 200, 'status' => 'success']);
            }
        }
    }


    /**
     * @Rest\Get("/nota/data", name="nota_data")
     * @OA\Response(
     *     response=200,
     *     description="Los datos de una nota",
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="Id de la nota",
     *     @OA\Schema(type="integer")
     * )
     */
    public function dataAction(Request $request, NotaRepository $notaRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        if (
            is_null($request->get('id'))
        ) {
            return new JsonResponse(['message' => 'Faltan parámetros en la petición', 'code' => 400, 'status' => 'error']);
        } else {
            $id  = $request->get('id');
            $nota = $notaRepository->findOneById($id);

            if (is_null($nota)) {
                return new JsonResponse(['message' => 'No se ha encontrado la nota', 'code' => 404, 'satus' => 'error']);
            } else {

                return new JsonResponse(['data' => $nota->serializar(), 'code' => 200, 'status' => 'success']);
            }
        }
    }
}
