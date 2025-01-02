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
            'name' => 'o:label',
            'options' => [
                'label' => 'Label', // @translate
            ],
            'attributes' => [
                'id' => 'o:label',
                'required' => true,
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
        $this->add([
            'type' => LaminasElement\Checkbox::class,
            'name' => 'exclude_private',
            'options' => [
                'label' => 'Exclude private', // @translate
            ],
            'attributes' => [
                'id' => 'exclude_private',
            ],
        ]);
    }
}
