<?php
namespace WpGmap\core\model;

use WpGmap\core\Helper;
use WpGmap\core\Settings;

class MapSettings extends Model
{
    public function __construct()
    {
        parent::__construct();

        $this->table = Settings::getTableNames()->settings;
    }

    public function add($data)
    {
        $insertData = $this->transformData($data);

        return $this->insert($insertData);
    }

    public function transformData($data)
    {
        $data['data'] = Helper::jsonEncode($data['data']);

        return $data;
    }

    public function update($data)
    {
        $updateData = $this->transformData($data);
        $id = $updateData['id'];
        unset($updateData['id']);

        return $this->edit($updateData, ['id' => $id]);
    }

    public function getDefault()
    {
        return $this->db->get_row("SELECT * FROM $this->table WHERE `is_default` = 1");
    }

    public function getPosts() 
    {
        return $this->db->get_results("SELECT ID, post_title FROM {$this->db->prefix}posts WHERE post_type = 'post' AND post_status != 'auto-draft' ORDER by ID DESC");
    }
}