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
            'type' => LaminasElement\Text::class,
            'name' => 'theme',
            'options' => [
                'label' => 'Theme name', // @translate
            ],
            'attributes' => [
                'id' => 'theme',
                'value' => 'ananke',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Checkbox::class,
            'name' => 'exclude_media',
            'options' => [
                'label' => 'Exclude media', // @translate
            ],
            'attributes' => [
                'id' => 'exclude_media',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Checkbox::class,
            'name' => 'exclude_item_sets',
            'options' => [
                'label' => 'Exclude item sets', // @translate
            ],
            'attributes' => [
                'id' => 'exclude_item_sets',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Checkbox::class,
            'name' => 'exclude_pages',
            'options' => [
                'label' => 'Exclude pages', // @translate
            ],
            'attributes' => [
                'id' => 'exclude_pages',
            ],
        ]);
    }
}
