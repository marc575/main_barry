<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CouponRepository;
use App\Entity\Coupon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('index');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/change_password", name="reset_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $error = null;
        $success = null;

        if($request->isMethod(Request::METHOD_POST)){
            $result = $this->changePassword($request, $passwordEncoder);
            $success = $result[0];
            $error = $result[1];
        }

        return $this->render('security/reset_password.html.twig', compact("error", "success"));
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return array
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $password = $request->request->get('password');
        $newPassword = $request->request->get('newPassword');
        $error = null;
        $success = null;
        if( $passwordEncoder->isPasswordValid($this->getUser(), $password) ){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy(['name'=>$this->getUser()->getUsername()]);
            $user->setPassword($passwordEncoder->encodePassword($this->getUser(), $newPassword));
            $em->persist($user);
            $em->flush();
            $success = "Mot de passe modifié avec succès";
        }else{
            $error = "Mot de passe incorrect";
        }

        return [$success, $error];
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $error = null;
        $success = null;
        if($request->getMethod() == Request::METHOD_POST){
            $result = $this->registerUser($request, $encoder);
            $success = $result[0];
            $error = $result[1];
        }

        return $this->render('security/register.html.twig', compact('success', 'error'));
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return array
     */
    private function registerUser(Request $request, UserPasswordEncoderInterface $encoder){
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirmPassword');
        $error = null;
        $success = null;
        if(!$password || strlen($password) < 8)
            $error = "Le mot de passe doit comporter au moins 8 caractéres";
        if($password != $confirmPassword)
            $error = "Les mots de passe ne correspondent pas";
        if(!$name || strlen($name) < 3)
            $error = "Renseignez un nom correct";
        if(!preg_match("#.{2,}@[a-zA-Z]{2,}\.[a-zA-Z]{2,5}#", $email))
            $error = "Renseignez une adresse email correcte";


        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findByEmail($email);
        if($user)
            $error = "Cette adresse email est déjà associée à un compte";
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setPassword($encoder->encodePassword($user, $password));
        $em->persist($user);
        $em->flush();
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $success = "Compte créé avec succès";

        return [$success, $error];
    }

    /**
     * @Route("/forget_password", name="forget_password")
     */
    public function forgetPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer): Response
    {
        $error = null;
        $success = null;
        if($request->getMethod() == Request::METHOD_POST){
            $result = $this->resetAndSendPassword($request, $encoder, $mailer);
            $success = $result[0];
            $error = $result[1];
            if ($this->getUser()) {
                return new JsonResponse(["status"=>0, "mes"=>"Accès non autorisé"], 403);
            }
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }
        return $this->render('security/forget_password.html.twig',compact('success', 'error'));
    }

    private function resetAndSendPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer) {
        $error = null;
        $success = null;
        if($request->getMethod() == Request::METHOD_POST) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneByEmail($request->request->get("email", ""));
            if (!$user)
                $error = "Ce compte n'existe pas.";
            $password = $this->generateRandomPassword() . $user->getId();
            $user->setPassword($encoder->encodePassword($user, $password));

            $url = $this->generateUrl("login");
            $_message = (new \Swift_Message("Nouveau mot de passe"))
                ->setFrom('barrypronos225@main.barrysport.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'security/set_password.html.twig',
                        compact("user", "url", "password")
                    ),
                    'text/html'
                );
            $mailer->send($_message);

            $em->persist($user);
            $em->flush();
            $success = "Votre mot de passe a été reinitialisé. Consulter votre boîte mail pour voir votre mot de passe";
        }
        return [$success, $error];
    }

    private function generateRandomPassword(){
        $char = "abcdefghijklmnopqrstuvwxyz";
        $char .= strtoupper($char);
        $char .= "1234567890";
        $password = str_shuffle($char);
        return substr($password, 0, 8);
    }
}
