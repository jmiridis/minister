<?php

namespace App\Controller\Frontend;

use App\Entity\Contact;
use App\Entity\Faq;
use App\Entity\Testimonial;
use App\Form\ContactType;
use App\Repository\PictureRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/testimonials', name: 'app_testimonials')]
    public function testimonials(EntityManagerInterface $em): Response
    {
        return $this->render('site/testimonials.html.twig', [
            'testimonials' => $em->getRepository(Testimonial::class)->findAllActive()
        ]);
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(EntityManagerInterface $em): Response
    {
        return $this->render('site/faq.html.twig', [
            'faqs' => $em->getRepository(Faq::class)->findAllActive()
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        $entity = new Contact();
        $form   = $this->createCreateForm($entity);

        return $this->render('site/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/contact', name: 'app_contact_create', methods: ['POST'])]
    public function createAction(
        Request                  $request,
        EntityManagerInterface   $em,
        EventDispatcherInterface $dispatcher
    ): Response {
        $contact = new Contact();
        $form    = $this->createCreateForm($contact);
        $form->handleRequest($request);

        if ($form->isValid() and $this->captchaverify($request->get('g-recaptcha-response'))) {
            /** @var EntityManager $em */

            $em->getConnection()->beginTransaction();

            try {
                $em->persist($contact);
                $em->flush();
                $em->getConnection()->commit();

//                $dispatcher->dispatch(AppEvents::CONTACT_CREATED, new ContactEvent($contact));
                $request->getSession()->set('contact_id', $contact->getId());

                return $this->redirect($this->generateUrl('app_contact_success'));
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                return $this->redirect($this->generateUrl('app_contact_error'));
            }
        }

        if ($form->isSubmitted() && $form->isValid() && !$this->captchaverify($request->get('g-recaptcha-response'))) {
            $this->addFlash('error', 'Please check the box below');
        }

        return $this->render('site/contact.html.twig', [
            'entity' => $contact,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/thankYou', name: 'app_contact_success')]
    public function contactSuccessAction(Request $request, EntityManagerInterface $em): Response
    {
        if (null === $contactId = $request->getSession()->get('contact_id')) {
            throw new NotFoundHttpException('');
        }
        if (null === $contact = $em->getRepository(Contact::class)->find($contactId)) {
            throw new NotFoundHttpException('');
        }

        return $this->render('site/contactSuccess.html.twig', [
            'contact' => $contact
        ]);
    }

    #[Route('/', name: 'app_contact_error')]
    public function contactErrorAction(Request $request, EntityManagerInterface $em): Response
    {
        if (null === $contactId = $request->getSession()->get('contact_id')) {
            throw new NotFoundHttpException('');
        }
        if (null === $contact = $em->getRepository(Contact::class)->find($contactId)) {
            throw new NotFoundHttpException('');
        }

        return $this->render('site/contactError.html.twig', [
            'contact' => $contact
        ]);
    }

    private function createCreateForm(Contact $entity): FormInterface
    {
        $form = $this->createForm(ContactType::class, $entity, [
            'action' => $this->generateUrl('app_contact_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }


    private function captchaVerify($recaptcha)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "secret"   => "6Lcfs6wZAAAAAB7aoZOPN-SwvB0X5CJ-cXn8aW-U",
            "response" => $recaptcha
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }

}
