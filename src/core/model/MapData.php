<?php
namespace WpGmap\core\model;

use WpGmap\core\Helper;
use WpGmap\core\Settings;

class MapData extends Model
{
    public function __construct()
    {
        parent::__construct();

        $this->table = Settings::getTableNames()->data;
    }

    public function add($data)
    {
        $insertData = $this->transformData($data);

        return $this->insert($insertData);
    }

    public function transformData($data)
    {
        $data['data'] = Helper::jsonEncode($data['data']);
        $data['rawdata'] = Helper::jsonEncode($data['rawdata']);

        return $data;
    }

    public function update($data)
    {
        $updateData = $this->transformData($data);
        $id = $updateData['id'];
        unset($updateData['id']);

        return $this->edit($updateData, ['id' => $id]);
    }

    public function getPostsByIds($ids) 
    {
        $whereConditionArray = [];
        foreach ($ids as $id) {
            $whereConditionArray[] = "ID = '$id'";
        }

        $queryString = "SELECT ID, post_content, post_title, post_excerpt FROM {$this->db->prefix}posts WHERE post_type = 'post' AND post_status != 'auto-draft' AND (".implode(' OR ', $whereConditionArray).") ORDER by ID DESC";

        return $this->db->get_results($queryString);
    }
}