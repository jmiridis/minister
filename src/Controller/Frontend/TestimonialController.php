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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\CaptchaControllerTrait;
use App\Entity\Testimonial;
use App\Form\TestimonialType;

#[Route('/testimonials')]
class TestimonialController extends AbstractController
{
    use CaptchaControllerTrait;

    #[Route('', name: 'app_testimonials', methods: ['GET'])]
    public function testimonials(EntityManagerInterface $em): Response
    {
        return $this->render('frontend/testimonial/index.html.twig', [
            'testimonials' => $em->getRepository(Testimonial::class)->findAllActive(),
            'form'         => $this->createTestimonialForm(new Testimonial())->createView(),
        ]);
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    #[Route('', name: 'app_testimonial_create', methods: ['POST'])]
    public function createTestimonialAction(
        Request                $request,
        EntityManagerInterface $em,
        MailerInterface        $mailer
    ): Response {
        $testimonial = new Testimonial();
        $form        = $this->createTestimonialForm($testimonial);
        $form->handleRequest($request);

        if ($form->isValid() and $this->captchaverify($request->get('g-recaptcha-response'))) {
            /** @var EntityManager $em */

            $em->getConnection()->beginTransaction();

            try {
                $em->persist($testimonial);

                $email = (new Email())
                    ->from('info@minister-mayela.com')
                    ->to('mayela@miridis.com')
                    ->subject($testimonial->getName() . ' has submitted a testimonial')
                    ->text($testimonial->getContent())
                    ->html('<p>' . $testimonial->getContent() . '</p>')
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                ;

                $mailer->send($email);

                $em->flush();
                $em->getConnection()->commit();

                $request->getSession()->set('testimonial_id', $testimonial->getId());

                return $this->redirect($this->generateUrl('app_testimonial_success'));
            } catch (Exception $e) {
                $em->getConnection()->rollBack();

                // TODO: send error via email

                return $this->redirect($this->generateUrl('app_testimonial_success'));
            }
        }

        if ($form->isSubmitted() && $form->isValid() && !$this->captchaverify($request->get('g-recaptcha-response'))) {
            $this->addFlash('error', 'Please check the box below');
        }

        return $this->render('frontend/testimonial/index.html.twig', [
            'entity'       => $testimonial,
            'form'         => $form->createView(),
            'testimonials' => $em->getRepository(Testimonial::class)->findAllActive(),
        ]);
    }

    #[Route('/thankYouTestimonial', name: 'app_testimonial_success')]
    public function testimonialSuccessAction(Request $request, EntityManagerInterface $em): Response
    {
        if (null === $testimonialId = $request->getSession()->get('testimonial_id')) {
            throw new NotFoundHttpException('');
        }
        if (null === $testimonial = $em->getRepository(Testimonial::class)->find($testimonialId)) {
            throw new NotFoundHttpException('');
        }

        return $this->render('frontend/testimonial/success.html.twig', [
            'testimonial' => $testimonial
        ]);
    }

    private function createTestimonialForm(Testimonial $entity): FormInterface
    {
        $form = $this->createForm(TestimonialType::class, $entity, [
            'action' => $this->generateUrl('app_testimonial_create'),
            'method' => 'POST',
            'source' => 'frontend',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }
}
