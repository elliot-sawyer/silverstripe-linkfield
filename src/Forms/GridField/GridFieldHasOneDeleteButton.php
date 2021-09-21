<?php

namespace gorriecoe\LinkField\Forms\GridField;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\GridField\GridField_FormAction;
use JsonSchema\Exception\ValidationException;
use SilverStripe\Control\Controller;

class GridFieldHasOneDeleteButton implements GridField_HTMLProvider, GridField_ActionProvider
{

    /**
     * Fragment to write the button to
     */
    protected $targetFragment;

    /**
     * GridFieldHasOneUnlinkButton constructor.
     * @param DataObject $parent
     * @param string $targetFragment
     */
    public function __construct($targetFragment = 'buttons-before-right')
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * Get fragment to write the button to
     */
    public function getTargetFragment()
    {
        return $this->targetFragment;
    }

    /**
     * Set fragment to write the button to
     *
     * @param string $targetFragment
     * @return static
     */
    public function setTargetFragment($targetFragment)
    {
        $this->targetFragment = $targetFragment;
        return $this;
    }

    /**
     * @param GridField $gridField
     * @return array
     */
    public function getActions($gridField)
    {
        return ['deleterelation'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName !== 'deleterelation') {
            return;
        }

        $record = $gridField->getRecord();
        if (!$record || !$record->exists()) {
            return;
        }

        /** @var DataObject|null $item */
        $item = $gridField->getList()->byID($record->ID);
        if ($item === null) {
            return;
        }

        if (!$item->canDelete()) {
            throw new ValidationException(
                _t(__CLASS__ . '.EditPermissionsFailure', 'No delete permissions')
            );
        }

        $gridField->setRecord(null);
        $gridField->getList()->remove($item);
        $item->delete();

        Controller::curr()->getResponse()->setStatusCode(
            200,
            _t(__CLASS__ . '.Deleted', 'Deleted')
        );
    }

    public function getHTMLFragments($gridField)
    {
        $record = $gridField->getRecord();
        if (!$record || !$record->exists()) {
            return [];
        }

        $field = new GridField_FormAction(
            $gridField,
            'gridfield_deleterelation',
            _t(__CLASS__ . '.Delete', 'Delete'),
            'deleterelation',
            'deleterelation'
        );

        $field->setAttribute('data-icon', 'chain--plus')
            ->addExtraClass(
                'align-items-center d-flex btn btn-outline-secondary '
                    . 'font-icon-trash-bin action_gridfield_deleterelation'
            );

        return [
            $this->targetFragment => $field->Field(),
        ];
    }
}
