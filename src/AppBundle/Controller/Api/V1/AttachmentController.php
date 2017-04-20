<?php

namespace AppBundle\Controller\Api\V1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Entity\Email;
use AppBundle\Entity\Attachment;

class AttachmentController extends Controller
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
     * @Route("/emails/{id}/attachments", name="api_v1_email_attachment_new", requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function newAttachmentAction(Request $request, Email $email)
    {
        $attachment = new Attachment();
        $attachment->setEmail($email);
        $this->em->persist($attachment);
        $this->em->flush();

        if ($attachment_id = $attachment->getId()) {
            return $this->redirectToRoute('api_v1_email_attachment_record', [
                'id' => $email->getId(),
                'attachment_id' => $attachment_id,
            ], 201);
        }

        // if somehow record has not been created
        return $this->createNotFoundException();
    }

    /**
     * @Route("/emails/{id}/attachments/{attachment_id}", name="api_v1_email_attachment_record", requirements={"id": "\d+", "attachment_id": "\d+"})
     * @Method("GET")
     * @ParamConverter("attachment", options={"mapping": {"attachment_id": "id"}})
     */
    public function getEmailRecordAction(Request $request, Email $email, Attachment $attachment)
    {
        if ($attachment->getEmail()->getId() !== $email->getId()) {
            return new Response('', 404);
        }

        return $this->json([
            'status' => 'success',
            'results' => $this->get('app.serializer')->normalize($attachment),
        ]);
    }
}
