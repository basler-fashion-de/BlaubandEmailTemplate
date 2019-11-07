<?php

namespace BlaubandEmailTemplate\Installers;

use Shopware\Models\Mail\Mail;
use Shopware\Models\Attribute\Mail as MailAttribute;
use Shopware\Components\Model\ModelManager;
use BlaubandEmail\Services\ConfigService;

class Mails
{
    /** @var ModelManager */
    private $modelManager;

    /** @var array */
    private $mails;

    /** @var String */
    private $pluginRoot;

    public function __construct(ModelManager $modelManager, ConfigService $filesConfigService, $pluginRoot)
    {
        $this->modelManager = $modelManager;
        $this->mails = $filesConfigService->get('mails', true);
        $this->pluginRoot = $pluginRoot;
    }

    /**
     * @return void
     */
    public function install()
    {
        $repository = $this->modelManager->getRepository(Mail::class);

        foreach ($this->mails as $mail) {
            $mailModel = $repository->findOneBy(['name' => $mail['name']]);
            if (!$mailModel) {
                $mailModel = new Mail();
                $this->setMailModelData($mailModel, $mail);
                $this->modelManager->persist($mailModel);
            }
        }

        $this->modelManager->flush();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $repository = $this->modelManager->getRepository(Mail::class);

        foreach ($this->mails as $mail) {
            $mailModel = $repository->findOneBy(['name' => $mail['name']]);

            if($mailModel){
                $this->modelManager->remove($mailModel);
            }
        }

        $this->modelManager->flush();
    }

    /**
     * @return void
     */
    public function update()
    {
        //Diese Templates werden nicht mehr angepasst.
    }

    private function setMailModelData(Mail $mailModel, $data){
        $mailModel->setName($data['name']);
        $mailModel->setFromMail($data['fromMail']);
        $mailModel->setFromName($data['fromName']);
        $mailModel->setSubject($data['subject']);

        if(is_file($this->pluginRoot.$data['plainContent'])){
            $mailModel->setContent(file_get_contents($this->pluginRoot.$data['plainContent']));
        }else{
            $mailModel->setContent((string) $data['plainContent']);
        }

        if(is_file($this->pluginRoot.$data['htmlContent'])){
            $mailModel->setContentHtml(file_get_contents($this->pluginRoot.$data['htmlContent']));
        }else{
            $mailModel->setContentHtml((string) $data['htmlContent']);
        }

        $mailModel->setIsHtml(($data['isHtml'] == 'true'));
        $mailModel->setMailtype(Mail::MAILTYPE_USER);

        if(!$mailModel->getAttribute()){
            $attribute = new MailAttribute();
            $attribute->setMail($mailModel);
            $this->modelManager->persist($attribute);

            $mailModel->setAttribute($attribute);
        }

        $mailModel->getAttribute()->setBlaubandCustomTemplate(true);

        return $mailModel;
    }
}
