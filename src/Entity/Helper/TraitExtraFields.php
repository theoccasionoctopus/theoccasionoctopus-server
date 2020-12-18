<?php

namespace App\Entity\Helper;

trait TraitExtraFields
{

    /**
     * @return array
     */
    public function getExtraFields(): array
    {
        return $this->extra_fields ? $this->extra_fields : array();
    }

    public function getExtraFieldsKeys(): array
    {
        return $this->extra_fields ? array_keys($this->extra_fields) : array();
    }

    public function getExtraField(string $key): string
    {
        return $this->extra_fields && array_key_exists($key, $this->extra_fields) ? $this->extra_fields[$key] : '';
    }

    /**
     * @param array $extra_fields
     */
    public function setExtraFields(array $extra_fields): bool
    {
        $changes = false;
        if (count($extra_fields) != count($this->extra_fields ? $this->extra_fields : [])) {
            $changes = true;
        } else {
            foreach ($extra_fields as $k => $v) {
                if (!array_key_exists($k, $this->extra_fields) || $this->extra_fields[$k] != $v) {
                    $changes = true;
                }
            }
        }
        if ($changes) {
            $this->extra_fields = $extra_fields;
            return true;
        }
        return false;
    }

    public function setExtraField(string $key, string $value)
    {
        if (!$this->extra_fields) {
            $this->extra_fields = [];
        }
        $this->extra_fields[$key] = $value;
    }
}
