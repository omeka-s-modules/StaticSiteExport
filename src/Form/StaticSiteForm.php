<?php
namespace StaticSiteExport\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;

class StaticSiteForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'theme',
            'options' => [
                'label' => 'Theme', // @translate
                'value_options' => [
                    'default' => 'default',
                ],
            ],
            'attributes' => [
                'id' => 'theme',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Checkbox::class,
            'name' => 'include_private',
            'options' => [
                'label' => 'Include private resources', // @translate
            ],
            'attributes' => [
                'id' => 'include_private',
            ],
        ]);
    }
}
