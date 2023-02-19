<?php

namespace App\Controller\Backend;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;

#[Route('/gallery')]
class PictureController extends AbstractController
{
    #[Route('/', name: 'backend_gallery_index', methods: ['GET'])]
    public function index(PictureRepository $pictureRepository): Response
    {
        return $this->render('backend/picture/index.html.twig', [
            'pictures' => $pictureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'backend_gallery_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PictureRepository $pictureRepository): Response
    {
        $picture = new Picture();
        $form    = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureRepository->save($picture, true);

            return $this->redirectToRoute('backend_gallery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend/picture/new.html.twig', [
            'picture' => $picture,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'backend_gallery_show', methods: ['GET'])]
    public function show(Picture $picture): Response
    {
        return $this->render('backend/picture/show.html.twig', [
            'picture' => $picture,
        ]);
    }

    #[Route('/{id}/edit', name: 'backend_gallery_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Picture $picture, PictureRepository $pictureRepository): Response
    {
        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureRepository->save($picture, true);

            return $this->redirectToRoute('backend_gallery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend/picture/edit.html.twig', [
            'picture' => $picture,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}/activate', name: 'backend_picture_activate', methods: ['POST'])]
    public function activate(Request $request, Picture $picture, EntityManagerInterface $em): JsonResponse
    {
        $checked = filter_var($request->request->get('checked'), FILTER_VALIDATE_BOOLEAN);
        $picture->setIsActive($checked);
        $em->flush();

        return new JsonResponse(['checked' => $checked]);
    }

    #[Route('/{id}', name: 'backend_gallery_delete', methods: ['POST'])]
    public function delete(Request $request, Picture $picture, PictureRepository $pictureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $picture->getId(), $request->request->get('_token'))) {
            $pictureRepository->remove($picture, true);
        }

        return $this->redirectToRoute('backend_gallery_index', [], Response::HTTP_SEE_OTHER);
    }
}
