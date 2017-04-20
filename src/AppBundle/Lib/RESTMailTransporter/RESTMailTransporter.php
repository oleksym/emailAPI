<?php

namespace AppBundle\Lib\RESTMailTransporter;

use AppBundle\Exception\TODOException;

class RESTMailTransporter
{
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        throw new TODOException("TODO: RESTMailTransporter->send\n".$message);
    }
}
