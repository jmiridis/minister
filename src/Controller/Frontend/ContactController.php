<?php

namespace App\Controller\Frontend;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\CaptchaControllerTrait;
use App\Entity\Contact;
use App\Form\ContactType;

#[Route('/contact')]
class ContactController extends AbstractController
{
    use CaptchaControllerTrait;

    #[Route('/', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        $entity = new Contact();
        $form   = $this->createContactForm($entity);

        return $this->render('frontend/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route('/', name: 'app_contact_create', methods: ['POST'])]
    public function createContactAction(
        Request                $request,
        EntityManagerInterface $em
    ): Response {
        $contact = new Contact();
        $form    = $this->createContactForm($contact);
        $form->handleRequest($request);

        if ($form->isValid() and $this->captchaverify($request->get('g-recaptcha-response'))) {
            /** @var EntityManager $em */

            $em->getConnection()->beginTransaction();

            try {
                $em->persist($contact);
                $em->flush();
                $em->getConnection()->commit();

                $request->getSession()->set('contact_id', $contact->getId());

                return $this->redirect($this->generateUrl('app_contact_success'));
            } catch (Exception $e) {
                $em->getConnection()->rollBack();

                // TODO: send error via email

                return $this->redirect($this->generateUrl('app_contact_success'));
            }
        }

        if ($form->isSubmitted() && $form->isValid() && !$this->captchaverify($request->get('g-recaptcha-response'))) {
            $this->addFlash('error', 'Please check the box below');
        }

        return $this->render('frontend/contact/index.html.twig', [
            'entity' => $contact,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/thankYouContact', name: 'app_contact_success')]
    public function contactSuccessAction(Request $request, EntityManagerInterface $em): Response
    {
        if (null === $contactId = $request->getSession()->get('contact_id')) {
            throw new NotFoundHttpException('');
        }
        if (null === $contact = $em->getRepository(Contact::class)->find($contactId)) {
            throw new NotFoundHttpException('');
        }

        return $this->render('frontend/contact/success.html.twig', [
            'contact' => $contact
        ]);
    }

    private function createContactForm(Contact $entity): FormInterface
    {
        $form = $this->createForm(ContactType::class, $entity, [
            'action' => $this->generateUrl('app_contact_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

}
