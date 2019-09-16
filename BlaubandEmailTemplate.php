<?php

namespace BlaubandEmailTemplate;

use BlaubandEmail\Services\ConfigService;
use BlaubandEmailTemplate\Installers\Attributes;
use BlaubandEmailTemplate\Installers\Mails;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin BlaubandEmailTemplate.
 */
class BlaubandEmailTemplate extends Plugin
{

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('blauband_email_template.plugin_dir', $this->getPath());
        parent::build($container);
    }

    public function install(InstallContext $context)
    {
        (new Attributes(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models')
        ))->install();

        (new Mails(
            $this->container->get('models'),
            new ConfigService($this->getPath() . '/Resources/mails.xml'),
            $this->getPath()
        ))->install();
    }

    public function uninstall(UninstallContext $context)
    {
        if(!$context->keepUserData()){
            /* Die Email-Templates werden nicht gelöscht wegen dem Email-Log von Shopware.*/
            if(!version_compare($this->container->get('config')->get('version'), '5.6.0', '>=')){
                (new Mails(
                    $this->container->get('models'),
                    new ConfigService($this->getPath() . '/Resources/mails.xml'),
                    $this->getPath()
                ))->uninstall();
            }

            (new Attributes(
                $this->container->get('shopware_attribute.crud_service'),
                $this->container->get('models')
            ))->uninstall();
        }
    }
}
