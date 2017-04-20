<?php

namespace AppBundle\Controller\Api\V1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Email;

class EmailController extends Controller
{
    private $em;

    /**
     * setContainer method used instead of constructor.
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->em = $this->getDoctrine()->getManager();
    }

    /**
     * @Route("/emails", name="api_v1_emails")
     * @Method("GET")
     */
    public function getEmailsAction(Request $request)
    {
        $email_repository = $this->em->getRepository('AppBundle:Email');
        $items = $email_repository->findAll();

        return $this->json([
            'status' => 'success',
            'count' => count($items),
            'results' => $this->get('app.serializer')->normalize($items),
        ]);
    }

    /**
     * @Route("/emails", name="api_v1_emails_new")
     * @Method("POST")
     */
    public function newEmailAction(Request $request)
    {
        $email = new Email();
        $this->em->persist($email);
        $this->em->flush();

        if ($id = $email->getId()) {
            return $this->redirectToRoute('api_v1_email_record', [
                'id' => $id,
            ], 201);
        }

        // if somehow record has not been created
        return $this->createNotFoundException();
    }

    /**
     * @Route("/emails/{id}", name="api_v1_email_record", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function getEmailRecordAction(Request $request, Email $email)
    {
        return $this->json([
            'status' => 'success',
            'results' => $this->get('app.serializer')->normalize($email),
        ]);
    }

    /**
     * @Route("/emails/{id}", name="api_v1_email_record_modify", requirements={"id": "\d+"})
     * @Method({"PUT", "PATCH"})
     */
    public function modifyEmailRecordAction(Request $request, Email $email)
    {
        $form = $this->createFormBuilder($email, ['csrf_protection' => false])
            ->add('priority')
            ->add('provider')
            ->add('sender')
            ->add('recipients')
            ->add('subject')
            ->add('body')
            ->getForm();

        $form->submit([
            'priority' => $request->request->get('priority', $email->getPriority()),
            'provider' => $request->request->get('provider', $email->getProvider()),
            'sender' => $request->request->get('sender', $email->getSender()),
            'recipients' => $request->request->get('recipients', $email->getRecipients()),
            'subject' => $request->request->get('subject', $email->getSubject()),
            'body' => $request->request->get('body', $email->getBody()),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($email);
            $this->em->flush();

            return $this->json([
                'status' => 'success',
            ]);
        }

        $reponse_message = [];
        foreach ($form->getErrors(true) as $error) {
            $reponse_message[] = $error->getOrigin()->getName().': '.$error->getMessage();
        }

        return $this->json([
            'status' => 'error',
            'message' => $reponse_message,
        ], 404);
    }

    /**
     * @Route("/emails/{id}", name="api_v1_email_record_delete", requirements={"id": "\d+"})
     * @Method("DELETE")
     */
    public function deleteEmailRecordAction(Request $request, Email $email)
    {
        $this->em->remove($email);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
        ]);
    }

    /**
     * @Route("/emails/send", name="api_v1_emails_send")
     * @Method("POST")
     */
    public function sendEmailsAction(Request $request)
    {
        $items = $this->em->getRepository('AppBundle:Email')->findEmailsToSend();

        $count_sent = 0;
        $reponse_message = [];
        foreach ($items as $email) {
            $message = \Swift_Message::newInstance()
                ->setSubject($email->getSubject())
                ->setFrom($email->getSender())
                ->setTo($email->getRecipients())
                ->setBody($email->getBody(), 'text/html');
            try {
                $this->get('app.mailer')->setMailerClient($email->getProvider())->send($message);
                $email->setStatus(Email::STATUS_SENT);
                $email->setSentAt(new \DateTime('now'));
                $this->em->persist($email);
                $this->em->flush();
                ++$count_sent;
            } catch (\Exception $e) {
                $reponse_message[] = 'Email ID '.$email->getId().': '.$e->getMessage();
            }
        }

        return $this->json([
            'status' => 'success',
            'count' => count($items),
            'results' => [
                'count_sent' => $count_sent,
            ],
            'message' => $reponse_message,
        ]);
    }
}
