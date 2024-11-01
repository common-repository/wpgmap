<?php
namespace WpGmap\core;

class Permission
{
    const DELETE = 'delete_posts';
    const EDIT = 'edit_posts';
    const PUBLISH = 'publish_posts';
    const READ = 'read';
    
    private static function currentUserCan($capabilty)
    {
        return current_user_can($capabilty);
    }

    public static function canDelete()
    {
        return self::currentUserCan(self::DELETE);
    }

    public static function canEdit()
    {
        return self::currentUserCan(self::EDIT);
    }

    public static function canAdd()
    {
        return self::currentUserCan(self::PUBLISH);
    }

    public static function canRead()
    {
        return self::currentUserCan(self::READ);
    }
}