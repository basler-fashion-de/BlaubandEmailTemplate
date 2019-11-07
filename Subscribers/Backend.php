<?php

namespace BlaubandEmailTemplate\Subscribers;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Mail;

class Backend implements SubscriberInterface
{
    /**  @var string */
    private $pluginDirectory;

    /** @var ModelManager $modelManager */
    private $modelManager;

    /**
     * @param $pluginDirectory
     */
    public function __construct($pluginDirectory, ModelManager $modelManager)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->modelManager = $modelManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend_BlaubandEmail' => 'onBackendBlaubandEmail',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Mail' => 'onBackendMail'
        ];
    }

    public function onBackendBlaubandEmail(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_BlaubandEmail $subject */
        $subject = $args->getSubject();

        if ($subject->Request()->getActionName() == 'send') {
            /** @var \Enlight_View_Default $view */
            $view = $subject->View();

            $conn = $this->modelManager->getConnection();
            $stmt = $conn->query("
                SELECT m.id, m.name
                FROM s_core_config_mails m
                JOIN s_core_config_mails_attributes a ON m.id = a.mailID AND a.blauband_custom_template = 1      
            ");

            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $view->assign('templates', $templates);
            $view->addTemplateDir($this->pluginDirectory . '/Resources/views');
        }
    }


    /**
     * Diese Klasse überträgt die Attribute des kopierten Email-Templates
     * Dies wird von Shopware nicht automatisch gemacht.
     *
     * Ohne diese Funktion würden die Blauband Einstellungen nicht übernommen werden.
     *
     * @param \Enlight_Event_EventArgs $args
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onBackendMail(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_BlaubandEmail $subject */
        $subject = $args->getSubject();

        /** @var \Enlight_View_Default $view */
        $view = $subject->View();

        if (
            $subject->Request()->getActionName() === 'copyMail' &&
            $view->getAssign('success')
        ) {
            $sourceId = $subject->Request()->getParam('id');

            $repository = $this->modelManager->getRepository(Mail::class);
            $sourceModel = $repository->find($sourceId);

            $destinationModel = $repository->findOneBy(['name' => 'Copy of ' . $sourceModel->getName()]);
            if($destinationModel == null){
                $destinationModel = $repository->findOneBy([], ['id' => 'DESC']);
            }

            if (
                $destinationModel &&
                $sourceModel &&
                $sourceModel->getAttribute() &&
                $sourceModel->getAttribute()->getBlaubandCustomTemplate()
            ) {
                $attribute = clone $sourceModel->getAttribute();
                $attribute->setMailId($destinationModel->getId());
                $attribute->setMail($destinationModel);
                $attribute->setId(null);

                $this->modelManager->persist($attribute);
                $this->modelManager->flush($attribute);
            }
        }
    }
}