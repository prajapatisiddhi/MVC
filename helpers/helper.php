<?php  
class Helper {

    public static function headerLink($col, $label, $currentSort, $currentOrder, $search, $module = '', $action = 'list', $limit = 5  , $allowedSort ) {

        if(!in_array($col , $allowedSort)){
            return $label;
        }

        $newOrder = 'ASC';
        if ($currentSort === $col) {
            $newOrder = ($currentOrder === 'ASC') ? 'DESC' : 'ASC';
        }

        $q = "?module=" . urlencode($module) 
            . "&action=" . urlencode($action) 
            . "&sort=" . urlencode($col) 
            . "&order=" . urlencode($newOrder);

        if ($search !== '') {
            $q .= "&search=" . urlencode($search);
        }

        if (!empty($limit)) {
            $q .= "&limit=" . urlencode($limit);
        }

        $q .= "&page=1";

        return "<a href='{$q}' class='text-decoration-none'>{$label}</a>";
    }
}
?>