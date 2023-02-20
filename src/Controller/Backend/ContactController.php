<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Contact;
use App\Repository\ContactRepository;


#[Route('/contact')]
class ContactController extends AbstractController
{
    #[Route('/', name: 'backend_contact_index')]
    public function index(ContactRepository $repository): Response
    {
        return $this->render('backend/contact/index.html.twig', [
            'contacts' => $repository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'backend_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('backend/contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}', name: 'backend_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, ContactRepository $contactRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contact->getId(), $request->request->get('_token'))) {
            $contactRepository->remove($contact, true);
        }

        return $this->redirectToRoute('backend_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
