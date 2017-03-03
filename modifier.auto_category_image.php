<?/**
 * Smarty XTC auto_category_image plugin
 *
 * Type:     modifier
 * Name:     auto_category_image
 * Author:   Korbinian Kasberger
 */
$orig = '';

smarty_modifier_auto_category_image($link);

function smarty_modifier_auto_category_image($link){

    $str = substr($link, 0, -1);
    $link = substr( strrchr( $str, '/' ), 1 );
    return auto_cat_img($link);
}

function auto_cat_img($link) {
    global $orig;
    $orig = $link;
    $img = fetch_img($link);
    return DIR_WS_INFO_IMAGES . $img;

}

function fetch_img ($link) {
    global $orig;
    
    if($link != null){
        $img = get_image($link);
        if (!$img) {
            $next = goDeeper($link);
            if($next == null){
                return fetch_sideways($orig);
            }else{
                return fetch_img($next);
            }
        }else{
            return $img;
        }
    }else{
        return null;
    }
}


function fetch_sideways($orig){
    $sides = goSideways($orig);
    $pos=0;
    if(sizeof($sides)>0){
      return sideways_loop($sides,$pos);
    }else{
        return get_image(goDeeper($side));
    }
}


function sideways_loop($sides,$pos){
    $side = $sides[$pos];
    $image = get_image($side);
    if($image == null && get_image(goDeeper($side)) == null && $pos < 60){
        return sideways_loop($sides,$pos+1);
    }else if($image != null){
        return $image;
    }
    return;
}

function get_cat_status($id){
    $query = "
           SELECT c.categories_status FROM categories c
           WHERE c.categories_id = '" . $id . "'
        ";
    $get_status = xtc_db_query($query);
    $status = mysql_fetch_row($get_status)[0];
    return $status;
}
function goSideways($orig){
    $result = '';
    $get_current_id = xtc_db_query("
           SELECT c.categories_id FROM categories c
           LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id
           WHERE cd.gm_url_keywords = '" . $orig . "'
           LIMIT 1
        ");
    $current_id = mysql_fetch_row($get_current_id)[0];
    $status = get_cat_status($current_id);
    if(!$status){
        return;
    }
    $sql = "
        SELECT cd.gm_url_keywords FROM categories_description cd
        LEFT JOIN categories c ON c.categories_id = cd.categories_id
        WHERE c.parent_id = '" . $current_id . "'
        LIMIT 60
        ";
    $get_child_urls = xtc_db_query($sql);
    $urls = array();
    while($row = mysql_fetch_assoc($get_child_urls)){
           $urls[] = $row['gm_url_keywords'];
    }
    return $urls;
}

function get_image($link){
    $query = 
    "SELECT p.products_image
        FROM products p
        LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id
        LEFT JOIN categories_description cd ON cd.categories_id = pc.categories_id
        WHERE cd.gm_url_keywords = '" . $link . "'
        AND p.products_status = '1' 
        AND p.products_image != ''
        LIMIT 1";
    $getimage = xtc_db_query($query);
    $img = mysql_fetch_row($getimage)[0];
    return $img;
}

function goDeeper($link) {
    $get_current_id = xtc_db_query("
           SELECT c.categories_id FROM categories c
           LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id
           WHERE cd.gm_url_keywords = '" . $link . "'
           LIMIT 1
        ");
    $current_id = mysql_fetch_row($get_current_id)[0];

    $get_child_url = xtc_db_query("
        SELECT cd.gm_url_keywords FROM categories c
        LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id
        WHERE c.parent_id = '" . $current_id . "'
        LIMIT 1
        ");
    $child = mysql_fetch_row($get_child_url)[0];
    return $child;

}