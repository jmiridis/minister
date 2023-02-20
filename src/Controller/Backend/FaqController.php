<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Faq;
use App\Form\FaqType;
use App\Repository\FaqRepository;

#[Route('/faq')]
class FaqController extends AbstractController
{
    #[Route('/', name: 'backend_faq_index', methods: ['GET'])]
    public function index(FaqRepository $faqRepository): Response
    {
        return $this->render('backend/faq/index.html.twig', [
            'faqs' => $faqRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'backend_faq_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FaqRepository $faqRepository): Response
    {
        $faq = new Faq();
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faqRepository->save($faq, true);

            return $this->redirectToRoute('backend_faq_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend/faq/new.html.twig', [
            'faq' => $faq,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'backend_faq_show', methods: ['GET'])]
    public function show(Faq $faq): Response
    {
        return $this->render('backend/faq/show.html.twig', [
            'faq' => $faq,
        ]);
    }

    #[Route('/{id}/edit', name: 'backend_faq_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Faq $faq, FaqRepository $faqRepository): Response
    {
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faqRepository->save($faq, true);

            return $this->redirectToRoute('backend_faq_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend/faq/edit.html.twig', [
            'faq' => $faq,
            'form' => $form,
            'delete_form' => $this->createDeleteForm($faq)->createView()
        ]);
    }

    #[Route('/{id}', name: 'backend_faq_delete', methods: ['POST'])]
    public function delete(Request $request, Faq $faq, FaqRepository $faqRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$faq->getId(), $request->request->get('_token'))) {
            $faqRepository->remove($faq, true);
        }

        return $this->redirectToRoute('backend_faq_index', [], Response::HTTP_SEE_OTHER);
    }

    private function createDeleteForm(Faq $faq): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('backend_faq_delete', ['id' => $faq->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
