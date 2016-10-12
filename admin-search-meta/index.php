<?php
/*
Plugin Name: Admin SearchMetaTo
Plugin URI: http://www.t31os.co.cc/
Description: Extends the posts management search to also check against meta data.
Author: t31os
Version: 1.0.1
Author URI: http://www.t31os.co.cc/
*/
 
class post_searches_meta {
       
        function __construct() {
                add_filter( 'posts_where' , array( $this , 'where' ) );
                add_filter( 'posts_join' , array( $this , 'join' ) );
                add_filter( 'posts_groupby' , array( $this , 'group' ) );
        }
 
        function group( $groupby ) {
                global $wp_query, $wpdb;
 
                if( !is_admin() || !$wp_query->query_vars['s'] )
                        return $groupby;
 
                // we need to group on post ID
 
                $mygroupby = "{$wpdb->posts}.ID";
 
                if( preg_match( "/$mygroupby/", $groupby )) {
                        // grouping we need is already there
                        return $groupby;
                }
 
                if( !strlen(trim($groupby))) {
                        // groupby was empty, use ours
                        return $mygroupby;
                }
 
                // wasn't empty, append ours
                return $groupby . ", " . $mygroupby;
        }
 
        function where( $where ) {
                global $wp_query, $wpdb;
               
                if( !is_admin() || !$wp_query->query_vars['s'] )
                        return $where;
               
                $s = $wp_query->query_vars['s'];
               
                if(
                // If the where string contains what i'm looking for
                strpos( $where , 'AND (((' ) != false &&
                // Make sure there's no other matches in the string
                strpos( substr( $where , 10 ) , 'AND (((' ) == false )
                {       
                        // Was either strpos and str_replace or preg_*** functions, which are heavier ...
                        $where = str_replace( 'AND (((' , "AND ((({$wpdb->postmeta}.meta_key LIKE '%$s%') OR ({$wpdb->postmeta}.meta_value LIKE '%$s%') OR (" , $where );
                }
 
                return $where;
        }
        function join( $join ) {
                global $wp_query, $wpdb;
 
                if( is_admin() && isset( $wp_query->query_vars['s'] ) ) {
                        $join .= " LEFT JOIN " . $wpdb->postmeta . " ON " . $wpdb->posts . ".ID = " . $wpdb->postmeta . ".post_id ";
                }
                return $join;
        }
}
$post_searches_meta = new post_searches_meta();
?>
