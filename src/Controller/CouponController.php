<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\CouponImage;
use App\Entity\User;
use App\Entity\Emails;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CouponController extends AbstractController
{
    /**
     * @Route("/coupon", name="coupon")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $coupons = $em->getRepository(Coupon::class)->findBy(["isDeleted"=>false], ['createdAt'=>'desc']);;
        $users = $em->getRepository(User::class)->findAll();
        $emails = $em->getRepository(Emails::class)->findAll();
        return $this->render('coupon/index.html.twig', compact('coupons', 'emails', 'users'));
    }

    /**
     * @Route("/coupon/delete/{coupon}", name="delete_coupon")
     */
    public function deleteCoupon(Coupon $coupon) {
        $em = $this->getDoctrine()->getManager();
        $coupon->setIsDeleted(true);
        $em->persist($coupon);
        $em->flush();
        return $this->redirectToRoute('coupon');
    }

    /**
     * @Route("/coupon/new", name="new_coupon")
     */
    public function newCoupon(Request $request, \Swift_Mailer $mailer){
        $error = null;
        $success = null;
        if($request->isMethod(Request::METHOD_POST)){
            $images = $request->files->get('images');
            $auteur = $request->request->get('auteur');
            $bookmaker = $request->request->get('bookmaker');
            $code = $request->request->get('code');
            $message = $request->request->get('message');

            if(!$images)
                $error = "Ajoutez une image";
            if(!$bookmaker)
                $error = "Preciser le bookmaker";
            if(!$code)
                $error = "Renseignez le code du coupon";

            $coupon = new Coupon();
            $coupon->setUser($this->getUser());
            $coupon->setAuteur($auteur);
            $coupon->setBookmaker($bookmaker);
            $coupon->setCode($code);
            $coupon->setMessage($message);
            if($images && !empty($images)){
                $couponImages = [];
                try{
                    foreach ($images as $image){
                        $couponImage = new CouponImage();
                        $fileName = $this->uploadImage($image, $request);
                        $couponImage->setCoupons($coupon);
                        $couponImage->setFileName($fileName);
                        $couponImages[] = $couponImage;
                    }
                }catch (\Exception $e){
                    return new JsonResponse(['status'=>0, 'mes'=>$e->getMessage()]);
                }
                $coupon->setCouponImage($couponImages);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($coupon);
            $em->flush();

            $url = $this->generateUrl("index");
            $emails = $em->getRepository(Emails::class)->findAll();
            foreach ($emails as $user) {
                $_message = (new \Swift_Message("Nouveau coupon disponible"))
                    ->setFrom('barrypronos225@main.1-xb-et.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'coupon/send_email.html.twig', compact('user', 'url', 'message', 'bookmaker')),
                        'text/html'
                    );
                $mailer->send($_message);
            }

            $success ="Coupon ajouté avec succès";
        }
        return $this->render("coupon/new_coupon.html.twig", compact('error', 'success'));
    }

    /**
     * @param UploadedFile $file
     * @throws \Exception
     */
    private function uploadImage(UploadedFile $file, Request $request) {
        $imageAccepted = array("jpg", "png", "jpeg");
        if( in_array(strtolower($file->guessExtension()), $imageAccepted) ){
            $fileName = $this->getUniqueFileName().".".$file->guessExtension();
            $file->move($this->getParameter("images_directory"), $fileName);
            return $fileName;
        }else{
            throw new \Exception("Format d'image incorrect", 100);
        }
    }


    private function getUniqueFileName(){
        return md5(uniqid());
    }
}
