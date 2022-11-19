<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CambiarContrasenaType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    
    
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/user/crear", name="app_user_crear", methods={"GET", "POST"})
     */
    public function crear(Request $request): Response
    {
        $titulo = 'Registrar Usuario';
        $usuario = new User();
        $form = $this->createForm(UserType::class, $usuario);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() and $form->isValid()) {
            $usuario = $form->getData();
            $contrasena = $usuario->getPassword();
            $usuario->setPassword($this->passwordEncoder->encodePassword(
                $usuario,
                $contrasena
            ));
            $em->persist($usuario);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }
        return $this->render('user/form.html.twig', [
            'titulo' => $titulo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/cambiar_contrasena", name="app_user_cambiar_contrasena", methods={"GET", "POST"})
     */
    public function cambiarContrasena(Request $request): Response
    {
        $titulo = 'Cambiar Contraseña';
        $form = $this->createForm(CambiarContrasenaType::class);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() and $form->isValid()) {

            $usuario = $this->getUser();
            $nuevaContrasena = $form->getData()['password'];;
            $usuario->setPassword($this->passwordEncoder->encodePassword(
                $usuario,
                $nuevaContrasena
            ));
            $em->persist($usuario);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/cambiarContrasena.html.twig', [
            'titulo' => $titulo,
            'form' => $form->createView()
        ]);
    }
}
