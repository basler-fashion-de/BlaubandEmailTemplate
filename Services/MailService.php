<?php

namespace BlaubandEmailTemplate\Services;

use BlaubandEmail\Services\MailServiceInterface;
use Shopware\Models\Mail\Mail;

class MailService implements MailServiceInterface
{
    /** @var MailServiceInterface */
    private $decoratedService;

    public function __construct(MailServiceInterface $decoratedService)
    {
        $this->decoratedService = $decoratedService;
    }

    public function saveMail(\Enlight_Components_Mail $mail)
    {
        $this->decoratedService->saveMail($mail);
    }

    public function sendMail($to, $bcc, $context, $isHtml, $files = [], $template = 'EKS-Template')
    {
        $request = Shopware()->Container()->get('front')->Request();
        $templateId = $request->get('template');
        $templateModel = Shopware()->Container()->get('models')->find(Mail::class, $templateId);

        if($templateModel){
            $template = $templateModel->getName();
        }

        $this->decoratedService->sendMail($to, $bcc, $context, $isHtml, $files, $template);
    }

}