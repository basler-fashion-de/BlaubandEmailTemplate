<?php

namespace BlaubandEmailTemplate\Subscribers;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

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
            'Enlight_Controller_Action_PreDispatch_Backend_BlaubandEmail' => 'onBackendBlaubandEmail'
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
}