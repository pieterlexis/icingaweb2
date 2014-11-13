<?php
// {{{ICINGA_LICENSE_HEADER}}}
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Module\Monitoring\Form\Setup;

use Icinga\Web\Form;
use Icinga\Web\Form\Element\Note;
use Icinga\Form\Config\Resource\DbResourceForm;

class IdoResourcePage extends Form
{
    public function init()
    {
        $this->setName('setup_monitoring_ido');
    }

    public function createElements(array $formData)
    {
        $this->addElement(
            'hidden',
            'type',
            array(
                'required'  => true,
                'value'     => 'db'
            )
        );
        $this->addElement(
            new Note(
                'title',
                array(
                    'value'         => mt('monitoring', 'Monitoring IDO Resource', 'setup.page.title'),
                    'decorators'    => array(
                        'ViewHelper',
                        array('HtmlTag', array('tag' => 'h2'))
                    )
                )
            )
        );
        $this->addElement(
            new Note(
                'description',
                array(
                    'value' => mt(
                        'monitoring',
                        'Please fill out the connection details below to access'
                        . ' the IDO database of your monitoring environment.'
                    )
                )
            )
        );

        if (isset($formData['skip_validation']) && $formData['skip_validation']) {
            $this->addSkipValidationCheckbox();
        } else {
            $this->addElement(
                'hidden',
                'skip_validation',
                array(
                    'required'  => true,
                    'value'     => 0
                )
            );
        }

        $livestatusResourceForm = new DbResourceForm();
        $this->addElements($livestatusResourceForm->createElements($formData)->getElements());
        $this->getElement('name')->setValue('icinga_ido');
    }

    public function isValid($data)
    {
        if (false === parent::isValid($data)) {
            return false;
        }

        if (false === isset($data['skip_validation']) || $data['skip_validation'] == 0) {
            if (false === DbResourceForm::isValidResource($this)) {
                $this->addSkipValidationCheckbox();
                return false;
            }
        }

        return true;
    }

    /**
     * Add a checkbox to the form by which the user can skip the connection validation
     */
    protected function addSkipValidationCheckbox()
    {
        $this->addElement(
            'checkbox',
            'skip_validation',
            array(
                'required'      => true,
                'label'         => t('Skip Validation'),
                'description'   => t('Check this to not to validate connectivity with the given database server')
            )
        );
    }
}