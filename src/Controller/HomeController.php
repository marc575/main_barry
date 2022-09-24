<?php

namespace App\Controller;

use App\Entity\Coupon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Emails;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $coupons = $em->getRepository(Coupon::class)->findBy(["isDeleted"=>false], ['createdAt'=>'desc']);
        $emails = $em->getRepository(Emails::class)->findAll();
        return $this->render('home/index.html.twig', compact( 'users', 'coupons', 'emails'));
    }

    /**
     * @Route("/email", name="email")
     */
    public function email(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $coupons = $em->getRepository(Coupon::class)->findBy(["isDeleted"=>false], ['createdAt'=>'desc']);;
        $emails = $em->getRepository(Emails::class)->findAll();
        return $this->render('home/email.html.twig', compact('emails', 'users', 'coupons'));
    }
}
