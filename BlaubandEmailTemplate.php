<?php

namespace BlaubandEmailTemplate;

use BlaubandEmailTemplate\Installers\Attributes;
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
    }

    public function uninstall(UninstallContext $context)
    {
        (new Attributes(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models')
        ))->uninstall();
    }
}
