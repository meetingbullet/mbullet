<?php 
echo theme_view('header'); 
echo isset($content) ? $content : Template::content();
// echo Front_Contexts::render_menu('text', 'normal'); 
echo theme_view('footer', array('show' => false));
?>