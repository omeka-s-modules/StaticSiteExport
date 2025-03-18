<?php
namespace StaticSiteExport\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;

class StaticSiteForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'base_url',
            'options' => [
                'label' => 'Base URL', // @translate
            ],
            'attributes' => [
                'id' => 'base_url',
                'value' => 'https://example.org/',
            ],
        ]);

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
    }
}
