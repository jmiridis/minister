<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'backend')]
    #[Route('/home', name: 'backend_home')]
    public function home(): Response
    {
        return $this->render('backend/home.html.twig');
    }
}
