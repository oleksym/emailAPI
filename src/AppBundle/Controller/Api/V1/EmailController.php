<?php

namespace AppBundle\Controller\Api\V1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EmailController extends Controller
{
    /**
     * @Route("/emails", name="api_v1_emails")
     * @Method("GET")
     */
    public function getEmailsAction(Request $request)
    {
        $email_repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Email');
        $items = $email_repository->findAll();

        return $this->json([
            'status' => 'success',
            'results' => $items,
        ]);
    }
}
