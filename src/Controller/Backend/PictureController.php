<?php

namespace App\Controller\Backend;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
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
    #[Route('/', name: 'backend_picture_index', methods: ['GET'])]
    public function index(PictureRepository $pictureRepository): Response
    {
        return $this->render('backend/picture/index.html.twig', [
            'pictures' => $pictureRepository->findAllSorted(),
        ]);
    }

    #[Route('/new', name: 'backend_picture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PictureRepository $pictureRepository): Response
    {
        $picture = new Picture();
        $form    = $this->createForm(PictureType::class, $picture);
        try {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $pictureRepository->save($picture, true);
                $pictureRepository->insert($picture);

                return $this->redirectToRoute('backend_picture_index', [], Response::HTTP_SEE_OTHER);
            }
        } catch (Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->render('backend/picture/new.html.twig', [
            'picture' => $picture,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'backend_picture_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Picture $picture, #[Autowire('%kernel.project_dir%')] $projectDir): Response
    {
        $filePath = "$projectDir/public/images/gallery/" . $picture->getImage();
        $exifData = exif_read_data($filePath, null, true);
        $data     = [
            'filename' => $exifData['FILE']['FileName'],
            'filetype' => $exifData['FILE']['MimeType'],
            'height'   => $exifData['COMPUTED']['Height'],
            'width'    => $exifData['COMPUTED']['Width']
        ];

        return $this->render('backend/picture/show.html.twig', [
            'picture'   => $picture,
            'exif_data' => $data
        ]);
    }

    #[Route('/{id}/edit', name: 'backend_picture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Picture $picture, PictureRepository $pictureRepository): Response
    {
        $previousPicture  = clone $picture;
        $previousPosition = $previousPicture->getPosition();
        $form             = $this->createForm(PictureType::class, $picture);

        try {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $newPosition = $picture->getPosition();
                $picture->setPosition($previousPosition);
                $pictureRepository->save($picture, true);

                if ($previousPosition !== $newPosition) {
                    $pictureRepository->moveToPosition($picture, $newPosition);
                }

                return $this->redirectToRoute('backend_picture_index', [], Response::HTTP_SEE_OTHER);
            }
        } catch (Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->render('backend/picture/edit.html.twig', [
            'picture' => $picture,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}/sort/{position}', name: 'backend_picture_sort', methods: ['POST'])]
    public function sortAction(Picture $picture, int $position, PictureRepository $repo): JsonResponse
    {
        // the provided picture must be put at the specified position moving all the other pictures down
        try {
            $repo->moveToPosition($picture, $position + 1);

            return new JsonResponse(['rc' => 200]);
        } catch (\Exception $e) {
            return new JsonResponse(['rc' => 500, 'message' => $e->getMessage()]);
        }
    }

    #[Route('/{id}/activate', name: 'backend_picture_activate', methods: ['POST'])]
    public function activate(Request $request, Picture $picture, EntityManagerInterface $em): JsonResponse
    {
        $checked = filter_var($request->request->get('checked'), FILTER_VALIDATE_BOOLEAN);
        $picture->setIsActive($checked);
        $em->flush();

        return new JsonResponse(['checked' => $checked]);
    }

    #[Route('/{id}', name: 'backend_picture_delete', methods: ['POST'])]
    public function delete(Request $request, Picture $picture, PictureRepository $pictureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $picture->getId(), $request->request->get('_token'))) {
            $pictureRepository->remove($picture, true);
        }

        return $this->redirectToRoute('backend_picture_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/pending', name: 'backend_pictures_pending', methods: ['GET'])]
    public function pendingAction(#[Autowire('%kernel.project_dir%')] $projectDir): Response
    {
        try {
            $pendingPath = "$projectDir/uploads/pending";
            $finder      = new Finder();
            $finder->files()->in($pendingPath);

            $pendingFiles = [];
            foreach ($finder as $file) {
                $pendingFiles[] = $file->getFilename();
            }
            $tempFiles = [];
            foreach ($finder as $file) {
                $tempFiles[] = $file->getFilename();
            }

            return $this->render('backend/picture/pending.html.twig', [
                'pendingFiles' => $pendingFiles,
                'tempFiles'    => $tempFiles,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['rc' => 500, 'message' => $e->getMessage()]);
        }
    }
}
