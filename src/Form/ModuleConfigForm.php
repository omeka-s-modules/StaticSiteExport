<?php
namespace StaticSiteExport\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;
use Laminas\Validator\Callback;
use StaticSiteExport\Module;

class ModuleConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'sites_directory_path',
            'options' => [
                'label' => 'Sites directory path', // @translate
                'info' => 'Enter the path to the directory where your static sites will be saved. The path must exist and be writable by the web server.', // @translate
            ],
            'attributes' => [
                'id' => 'sites_directory_path',
                'required' => true,
            ],
        ]);
        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'sites_directory_path',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => [
                        'messages' => [
                            Callback::INVALID_VALUE => 'Invalid sites directory path. The path must exist and be writable by the web server.', // @translate
                        ],
                        'callback' => [Module::class, 'sitesDirectoryPathIsValid'],
                    ],
                ],
            ],
        ]);
    }
}
