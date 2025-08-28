<?php

namespace Werkbot\Seeder\SeederHistory;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
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
use SilverStripe\View\Requirements;
use Werkbot\Seeder\Settings\SeederSettings;

class SeederAdmin extends ModelAdmin
{
  private static $managed_models = [
    SeederSettings::class => [
      'title' => 'Settings',
    ],
    SeedObject::class => [
      'title' => 'History'
    ],
  ];

  private static $url_segment = 'seeder';
  private static $menu_title = 'Seeder';
  private static $menu_icon_class = 'font-icon-back-in-time';

  public function getEditForm($id = null, $fields = null)
  {
    $modelClass = $this->modelClass;

    if ($modelClass == SeederSettings::class) {
      Requirements::customCSS(<<<CSS
        .cms .cms-panel-padded .cms-content-view {
          padding: 0;
        }
      CSS);

      $seederSettings = SeederSettings::currentSeederSettings();
      $form = Form::create(
        $this,
        'EditForm',
        $seederSettings->getCMSFields(),
        FieldList::create(
          FormAction::create(
            'save_settings',
            _t('SilverStripe\\CMS\\Controllers\\CMSMain.SAVE', 'Save')
          )->addExtraClass('btn-primary font-icon-save')
        )
      )->setHTMLID('Form_EditForm');
      $form->addExtraClass('flexbox-area-grow fill-height cms-content cms-edit-form');
      $form->loadDataFrom($seederSettings);
      $form->setTemplate('SilverStripe/SiteConfig/Includes/SiteConfigLeftAndMain_EditForm');

      $this->extend('updateEditForm', $form);

      return $form;

    }

    $form = parent::getEditForm($id, $fields);

    $form->fields()->insertBefore(
      $this->sanitiseClassName($modelClass),
      HeaderField::create('Remove generated data by deleting the associated seed.')
    );

    $gridField = $form->fields()->fieldByName($this->sanitiseClassName($modelClass));

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

  /**
   * Save the current sites {@link SeederSettings} into the database.
   *
   * @param array $data
   * @param Form $form
   * @return String
   */
  public function save_settings($data, $form)
  {
    $seederSettings = SeederSettings::currentSeederSettings();
    $form->saveInto($seederSettings);
    $seederSettings->write();
    $this->response->addHeader(
      'X-Status',
      rawurlencode(_t('SilverStripe\\Admin\\LeftAndMain.SAVEDUP', 'Saved.'))
    );
    return $form->forTemplate();
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

