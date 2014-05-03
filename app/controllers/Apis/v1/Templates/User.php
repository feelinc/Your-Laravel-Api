<?php
namespace Apis\v1\Templates;

class User extends Template
{

    /**
     * Prepare the data to retreive.
     *
     * @return void
     */
    public function prepareData()
    {
        $this->setAttribute('id');
        $this->setAttribute('email');
        $this->setAttribute('first_name');
        $this->setAttribute('last_name');
        $this->setAttribute('display_name', null, trim($this->first_name.' '.$this->last_name));
        $this->setAttribute('last_login');
        $this->setAttribute('is_activated', null, $this->getModel()->activated);
        $this->setAttribute('created_at', 'registered_at');
        $this->setAttribute('updated_at');

        // Get the user groups
        $groupNames = array();
        $groups = $this->getModel()->getGroups();

        if ( ! $groups->isEmpty()) {
            foreach ($groups as $group) {
                $groupNames[] = $group->name;
            }
        }

        unset($groups);

        $this->setAttribute('groups', null, $groupNames);

        unset($groupNames);
    }

}
