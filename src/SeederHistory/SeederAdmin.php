<?php

namespace Werkbot\Seeder;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\HeaderField;

class SeederAdmin extends ModelAdmin
{
    private static $managed_models = [
        SeedObject::class => [
            'title' => 'Seeder History'
        ],
    ];

    private static $url_segment = 'seeder';
    private static $menu_title = 'Seeder';
    private static $menu_icon_class = 'font-icon-back-in-time';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $form->fields()->insertBefore(
            $this->sanitiseClassName($this->modelClass),
            HeaderField::create('Remove generated data by deleting the associated seed.')
        );

        $gridField = $form->fields()->fieldByName($this->sanitiseClassName($this->modelClass));

        $config = GridFieldConfig::create();
        $config->addComponent(new GridFieldButtonRow('before'))
            ->addComponent(new GridFieldDataColumns())
            ->addComponent(new GridFieldSortableHeader())
            ->addComponent(new GridFieldFilterHeader())
            ->addComponent(new GridFieldEditButton())
            ->addComponent((new GridFieldDetailForm())->setShowAdd(false))
            ->addComponent(new GridField_ActionMenu())
            ->addComponent(new GridFieldDeleteAction());

        $gridField->setConfig($config);

        return $form;
    }

    public function canView($member = null)
    {
        $canView = parent::canView($member = null);
        /*
            Only show seeder admin if in a dev or test environment
        */
        if (!(Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev' || Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'test')) {
            $canView = false;
        }
        return $canView;
    }
}
