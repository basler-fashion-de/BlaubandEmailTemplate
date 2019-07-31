<?php

namespace BlaubandEmailTemplate\Installers;

use BlaubandEmail\Models\LoggedMail;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Mail\Repository;

class Attributes
{
    /**
     * @var CrudService
     * */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;


    /**
     * Attributes constructor.
     * @param CrudService $crudService
     * @param ModelManager $modelManager
     */
    public function __construct(CrudService $crudService, ModelManager $modelManager)
    {
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    /**
     * @return void
     */
    public function install()
    {
        $this->update();

        /** @var Repository $repository */
        $repository = $this->modelManager->getRepository(Mail::class);

        /** @var Mail $blaubandMail */
        $blaubandMail = $repository->findOneBy(['name' => 'blaubandMail']);

        if($blaubandMail === null){
            $blaubandMail = $repository->findOneBy(['name' => 'Blauband Mail']);
        }

        if($blaubandMail){
            $attributes = $blaubandMail->getAttribute();
            if(!$attributes){
                $attributes = new \Shopware\Models\Attribute\Mail();
                $attributes->setMailId($blaubandMail->getId());
                $this->modelManager->persist($attributes);
            }
            $attributes->setBlaubandCustomTemplate(true);
            $this->modelManager->flush($attributes);
        }
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        try{
            $this->crudService->delete(
                's_core_config_mails_attributes',
                'blauband_custom_template',
                true
            );

            $metaDataCache = $this->modelManager->getConfiguration()->getMetadataCacheImpl();
            $metaDataCache->deleteAll();
            $this->modelManager->generateAttributeModels(['s_core_config_mails_attributes']);
        }catch (\Exception $e){
            //Es gab Fälle in dem das deinstallieren nicht klappte.
            //In Zukunft haben wir lieber Datenmüll als dass die Deinstallation nicht geht
        }

    }

    /**
     * @return void
     */
    public function update()
    {
        $this->crudService->update(
            's_core_config_mails_attributes',
            'blauband_custom_template',
            TypeMapping::TYPE_BOOLEAN,

            [
                'label' => 'Blauband Custom Mailvorlage',
                'supportText' => 'Wenn aktiv, kann diese Vorlage beim Versenden von personalisierten Email verwendet werden.',
                'displayInBackend' => false,
                'custom' => false,
            ]
        );

        $metaDataCache = $this->modelManager->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        $this->modelManager->generateAttributeModels(['s_core_config_mails_attributes']);
    }
}
