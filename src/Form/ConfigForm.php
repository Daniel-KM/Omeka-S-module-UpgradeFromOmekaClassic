<?php
namespace UpgradeFromOmekaClassic\Form;

use Zend\Form\Element\Checkbox;
use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'upgrade_add_old_routes',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Add aliases for old record urls and the home page', // @translate
                'info' => 'Set aliases for items/show/#id to item/#id, and the same for collections, files and the home page. Note: a default site must be set. For more advanced aliases, use the module Clean Url.', // @translate
            ],
        ]);
    }
}
