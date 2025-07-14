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
                'info' => 'Enter the absolute URL of your published site including the protocol, host, path, and a trailing slash. This is optional and can be set after export, prior to build, in hugo.json under baseURL.', // @translate
            ],
            'attributes' => [
                'id' => 'base_url',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'theme',
            'options' => [
                'label' => 'Theme', // @translate
                'info' => 'Select an Omeka theme to use to style your site. This is optional and can be set after export, prior to build, in hugo.json under params.theme.',
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
                'info' => 'Check to include private resources in the export. The default behavior is to exclude private resources. This must be set prior to export.'
            ],
            'attributes' => [
                'id' => 'include_private',
            ],
        ]);
    }
}
