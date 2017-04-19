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
            'results' => $this->get('serializer')->normalize($items),
        ]);
    }

    /**
     * @Route("/emails/{id}", name="api_v1_email_record", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function getEmailRecordAction(Request $request, Email $email)
    {
        return $this->json([
            'status' => 'success',
            'results' => $this->get('serializer')->normalize($email),
        ]);
    }
}
