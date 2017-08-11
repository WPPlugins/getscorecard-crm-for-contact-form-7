<?php
/**
 * Created by PhpStorm.
 * User: Antonshell
 * Date: 19.10.2015
 * Time: 15:41
 */

class GetScorecardForm{

    const post_type = 'wpcf7_contact_form';

    public function createForm(){

        $template = new GetScorecardFormTemplate();

        $title = 'GetScorecard #' . substr(md5(time()),0,4);

        $properties = array(
            'form' => '',
            'mail' => array(),
            'mail_2' => array(),
            'messages' => array(),
            'additional_settings' => ''
        );

        foreach ( $properties as $key => $value ) {
            $properties[$key] = $template::get_default( $key );
        }

        $post_content = implode( "\n", wpcf7_array_flatten( $properties ) );

        $post_id = wp_insert_post( array(
            'post_type' => self::post_type,
            'post_status' => 'publish',
            //'post_title' => $this->title,
            'post_title' => $title,
            'post_content' => trim( $post_content )
        ));

        if ( $post_id ) {
            /* save wp_postmeta */
            foreach ( $properties as $prop => $value ) {
                update_post_meta(
                    $post_id, '_' . $prop,
                    wpcf7_normalize_newline_deep( $value )
                );
            }
            /**/

            /* save wp_options*/
            $options = $template->getOptions($post_id);

            foreach($options as $name=>$value){
                update_option($name, $value);
            }
            /**/
        }

        return $post_id;
    }

    function wpcf7_array_flatten( $input ) {
        if ( ! is_array( $input ) )
            return array( $input );

        $output = array();

        foreach ( $input as $value )
            $output = array_merge( $output, wpcf7_array_flatten( $value ) );

        return $output;
    }
}