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
            'name' => 'directory_path',
            'options' => [
                'label' => 'Directory path', // @translate
            ],
            'attributes' => [
                'id' => 'directory_path',
                'required' => true,
            ],
        ]);
        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'directory_path',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => [
                        'messages' => [
                            Callback::INVALID_VALUE => 'Invalid directory path. The directory path must exist and be writable by the web server.', // @translate
                        ],
                        'callback' => [Module::class, 'directoryPathIsValid'],
                    ],
                ],
            ],
        ]);
    }
}
