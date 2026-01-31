<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['key', 'value', 'type', 'group', 'label', 'description'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getValue($key, $default = null)
    {
        $setting = $this->where('key', $key)->first();
        return $setting ? $setting['value'] : $default;
    }

    public function setValue($key, $value)
    {
        $setting = $this->where('key', $key)->first();
        
        if ($setting) {
            return $this->update($setting['id'], ['value' => $value]);
        }
        
        return $this->insert(['key' => $key, 'value' => $value]);
    }

    public function getByGroup($group)
    {
        return $this->where('group', $group)->findAll();
    }

    public function getAllAsArray()
    {
        $settings = $this->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        
        return $result;
    }

    public function updateSettings($data)
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, $value);
        }
        return true;
    }
}
