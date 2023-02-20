<?php

namespace App\Controller\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Faq;
use App\Repository\PictureRepository;

class SiteController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/', name: 'app_home_no_locale')]
    public function index(): Response
    {
        return $this->render('site/home.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }

    #[Route('/aboutMe', name: 'app_aboutMe')]
    public function aboutMe(): Response
    {
        return $this->render('site/aboutMe.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }

    #[Route('/gallery', name: 'app_gallery')]
    public function gallery(PictureRepository $repository): Response
    {
        return $this->render('site/gallery.html.twig', [
            'pictures' => $repository->findAllActive(),
        ]);
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(EntityManagerInterface $em): Response
    {
        return $this->render('site/faq.html.twig', [
            'faqs' => $em->getRepository(Faq::class)->findAllActive()
        ]);
    }
}
