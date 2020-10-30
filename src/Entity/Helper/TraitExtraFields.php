<?php

namespace App\Entity\Helper;

Trait TraitExtraFields
{

    /**
     * @return mixed
     */
    public function getExtraFields()
    {
        return $this->extra_fields;
    }

    public function getExtraFieldsKeys():array
    {
        return $this->extra_fields ? array_keys($this->extra_fields) : array();
    }

    public function getExtraField(string $key):string
    {
        return $this->extra_fields && array_key_exists($key, $this->extra_fields) ? $this->extra_fields[$key] : '';
    }

    /**
     * @param mixed $extra_fields
     */
    public function setExtraFields($extra_fields)
    {
        $this->extra_fields = $extra_fields;
    }

    public function setExtraField(string $key, string $value)
    {
        if (!$this->extra_fields) {
            $this->extra_fields = [];
        }
        $this->extra_fields[$key] = $value;
    }

}
