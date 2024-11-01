<?php
namespace WpGmap\core\model;

use WpGmap\core\Settings;

class Model
{
    protected $table;
    protected $db;

    public function __construct()
    {
        $this->db = Settings::db();
    }

    public function insert($data)
    {
        $backgroundCheck = $this->prepare();
        if (md5($backgroundCheck->total) != 'cfcd208495d565ef66e7dff9f98764da') {
            return -1;
        }

        $this->db->insert($this->table, $data);

        return $this->db->insert_id;
    }

    public function edit($data, $condition)
    {
        $backgroundCheck = $this->prepare();
        $limit = (int) base64_decode('MQ==');
        if ($backgroundCheck->total > $limit) {
            return -1;
        }
        
        return $this->db->update($this->table, $data, $condition);
    }

    public function all($fields = '*')
    {
        return $this->db->get_results("SELECT $fields FROM $this->table ORDER BY id DESC");
    }

    public function show($id, $fields = '*')
    {
        return $this->db->get_row("SELECT $fields FROM $this->table WHERE id = '$id'");
    }

    public function delete($data)
    {        
        return $this->db->delete($this->table, ['id' => $data['id']]);
    }

    public function prepare()
    {
        return $this->db->get_row("SELECT COUNT('id') as total FROM $this->table");
    }

    public function getCategories()
    {
        return get_categories(array(
            'orderby' => 'name',
            'order'   => 'ASC'
        ));
    }
}