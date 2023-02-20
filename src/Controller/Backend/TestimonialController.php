<?php

namespace App\Controller\Backend;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Testimonial;
use App\Form\TestimonialType;
use App\Repository\TestimonialRepository;

#[Route('/testimonial')]
class TestimonialController extends AbstractController
{
    #[Route('/', name: 'backend_testimonial_index', methods: ['GET'])]
    public function index(TestimonialRepository $testimonialRepository): Response
    {
        return $this->render('backend/testimonial/index.html.twig', [
            'testimonials' => $testimonialRepository->findAllSorted(),
        ]);
    }

    #[Route('/new', name: 'backend_testimonial_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TestimonialRepository $testimonialRepository): Response
    {
        $testimonial = new Testimonial();
        $form        = $this->createForm(TestimonialType::class, $testimonial, [
            'source' => 'backend',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $testimonialRepository->save($testimonial, true);

            return $this->redirectToRoute('backend_testimonial_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend/testimonial/new.html.twig', [
            'testimonial' => $testimonial,
            'form'        => $form,
        ]);
    }

    #[Route('/{id}', name: 'backend_testimonial_show', methods: ['GET'])]
    public function show(Testimonial $testimonial): Response
    {
        return $this->render('backend/testimonial/show.html.twig', [
            'testimonial' => $testimonial,
        ]);
    }

    #[Route('/{id}/edit', name: 'backend_testimonial_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request               $request,
        Testimonial           $testimonial,
        TestimonialRepository $testimonialRepository
    ): Response {
        $form = $this->createForm(TestimonialType::class, $testimonial, [
            'source' => 'backend',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $testimonialRepository->save($testimonial, true);

            return $this->redirectToRoute('backend_testimonial_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend/testimonial/edit.html.twig', [
            'testimonial' => $testimonial,
            'form'        => $form,
            'delete_form' => $this->createDeleteForm($testimonial)->createView(),
        ]);
    }

    #[Route('/{id}/sort/{position}', name: 'backend_testimonial_sort', methods: ['POST'])]
    public function sortAction(Testimonial $testimonial, int $position, EntityManagerInterface $em): JsonResponse
    {
        try {
            $testimonial->setPosition($position);
            $em->persist($testimonial);
            $em->flush();

            return new JsonResponse(['rc' => 200]);
        } catch (\Exception $e) {
            return new JsonResponse(['rc' => 500, 'message' => $e->getMessage()]);
        }
    }

    #[Route('/{id}/activate', name: 'backend_testimonial_activate', methods: ['POST'])]
    public function activate(Request $request, Testimonial $testimonial, EntityManagerInterface $em): JsonResponse
    {
        $checked = filter_var($request->request->get('checked'), FILTER_VALIDATE_BOOLEAN);
        $testimonial->setIsActive($checked);
        $em->flush();

        return new JsonResponse(['checked' => $checked]);
    }


    #[Route('/{id}', name: 'backend_testimonial_delete', methods: ['POST'])]
    public function delete(
        Request               $request,
        Testimonial           $testimonial,
        TestimonialRepository $testimonialRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $testimonial->getId(), $request->request->get('_token'))) {
            $testimonialRepository->remove($testimonial, true);
        }

        return $this->redirectToRoute('backend_testimonial_index', [], Response::HTTP_SEE_OTHER);
    }

    private function createDeleteForm(Testimonial $testimonial): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('backend_testimonial_delete', ['id' => $testimonial->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
