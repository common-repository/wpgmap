<?php
namespace WpGmap\core;

class Helper
{
    public static function errorMessage($string)
    {
        echo '<br><div class="notice notice-warning"><p>'.esc_html($string).'</p></div>';
        exit();
    }

    public static function parseTemplate($string, $array)
    {
        $patterns = [];
        $replacements = [];

        foreach ($array as $key => $value) {
            $patterns[] = "/{{$key}}/";
            $replacements[] = $value;
        }

        return preg_replace($patterns, $replacements, $string);
    }

    public static function redirect($url, $time = 2)
    {
        echo '<p><em>You will be redirected in '.$time.' seconds or <a href="'.esc_url_raw($url).'">click here</a>.</em></p><script>setTimeout(function() { window.location.href = "'.esc_url_raw($url).'"; }, '.intval($time * 1000).');</script>';
        exit;
    }

    public static function jsonEncode($array)
    {
        return wp_json_encode($array);
    }

    public static function jsonDecode($jsonString)
    {
        return (object) json_decode($jsonString, JSON_UNESCAPED_UNICODE);
    }

    public static function coordinatesToArray($coordinates)
    {
        $result = [];
        $coordinateString = str_replace(['(', ')'], '', $coordinates);
        $coordinateStringArray = explode(',', $coordinateString);
        
        for ($i = 0; $i < count($coordinateStringArray); $i+=2) {
            $result[] = ['lat' => $coordinateStringArray[$i], 'lon' => $coordinateStringArray[$i+1]];
        }

        return $result;
    }

    public static function getLegends($legends)
    {
        $result = [];

        for ($i = 0; $i < count($legends['name']); $i++) {
            if (strlen($legends['name'][$i]) > 0) {
                $arr = explode('::', $legends['name'][$i]);
                $r = [
                    'name' => $arr[0],
                    'color' => $legends['color'][$i],
                ];

                if (isset($arr[1])) {
                    $r[$arr[1]] = true;
                }

                $result[] = $r;
            }
        }

        return $result;
    }

    public static function getPostIds($markings)
    {
        $result = [];

        foreach ($markings as $marking) {
            if (intval($marking['postid']) > 0) {
                $result[] = $marking['postid'];
            }
        }

        return array_values(array_unique($result));
    }

    public static function getPostById($id, $posts)
    {
        foreach ($posts as $post) {
            if ($post->ID == $id) {
                return $post;
            }
        }
    }

    public static function getPostAndCategory($posts, $legends)
    {
        $result = [];

        for ($i = 0; $i < count($posts); $i++) {
            $categoryIds = wp_get_post_categories($posts[$i]->ID);
            foreach ($categoryIds as $categoryId) {
                if (!isset($result[$categoryId])) {
                    $result[$categoryId] = [];
                    $result[$categoryId]['category_id'] = $categoryId;
                    $result[$categoryId]['name'] = get_cat_name($categoryId);
                    $result[$categoryId]['color'] = self::getLegendColorByName($result[$categoryId]['name'], $legends);
                    $result[$categoryId]['posts'] = [];
                }

                $result[$categoryId]['posts'][] = $posts[$i];
            }
        }

        // sort by category name
        $columns = array_column($result, 'name');
        array_multisort($columns, SORT_ASC, $result);

        // sort by category ID DESC
        // $columns = array_column($result, 'category_id');
        // array_multisort($columns, SORT_DESC, $result);

        return array_values($result);
    }

    public static function getLegendColorByName($name, $legends)
    {
        foreach ($legends as $legend) {
            if ($legend['name'] == $name) {
                return $legend['color'];
            }
        }
    }

    public static function shortText($text, $length = 190)
    {
        $text = strip_tags($text);

        if (strlen($text) > $length) {
            $text = substr($text, 0, ($length - 3)) . '...';
        }

        return $text;
    }

    public static function showTag($postId)
    {
        $names = [];
        $tags = get_the_tags($postId);

        if ($tags && count($tags) > 0) {
            foreach ($tags as $tag) {
                $names[] = $tag->name;
            }
        }

        return count($names) > 0 ? implode(', ', $names) : false;
    }

    public static function escJsonString($json)
    {
        return filter_var($json, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }

    public static function sanitizeArrayField($arrayField, $type = [])
    {
        if(is_array($arrayField)) {
            foreach ($arrayField as $key => &$value) {
                if (is_array($value)) {
                    $value = self::sanitizeArrayField($value);
                } else {
                    if (isset($type[$key])) {
                        if ($type[$key] == 'textarea') {
                            $value = sanitize_textarea_field($value);
                        }
                    } else {
                        $value = sanitize_text_field($value);
                    }
                }
            }
        }
    
        return $arrayField;
    }

    public static function escapeJson($jsonString)
    {
        return str_replace('&quot;', '"', esc_js($jsonString));
    }

    public static function verifyNonce()
    {
        return isset($_POST[Settings::get('prefix').'_field']) && wp_verify_nonce($_POST[Settings::get('prefix').'_field'], Settings::get('prefix').'_action');
    }
}