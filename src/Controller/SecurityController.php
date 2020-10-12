<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator)
    {
        // Instance de l'utilisateur
        $user = new User;

        // Init the form $errors array
        $errors = [];

        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ( $form->isSubmitted() ) 
        {
            // Controle auto du form (Validator)
            $errors = $validator->validate($user);

            if ($form->isValid())
            {
                // Récup des données du fromulaire "register"
                $data = $request->request->get('register');
    
                if ($data['agreeTerms'])
                {
                    $password = $passwordEncoder->encodePassword(
                        $user,
                        $data['password']['first']
                    );
    
                    $user->setPassword( $password );
                    $user->setBirthday( new \DateTime );
    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user); 
                    $em->flush();
                    
                    return $this->redirectToRoute('app_login');
                }
            }
        }

        $form = $form->createView();

        return $this->render('security/register.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
