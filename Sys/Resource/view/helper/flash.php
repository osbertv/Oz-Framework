<?php

/* 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		
 @filesource            App/Resource/view/flash.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 */

if (flash()->toasts || flash()->alerts) {
    //echo "<div style=\"position: absolute; top: 50px; right: 10px;\">";
    echo "<div class=\"mr-2 float-right\" style=\"position: -webkit-sticky;position: sticky;top:0;z-index:99;\">";
    foreach (flash()->toasts as $k => $v) {
        echo $this->include('helper/flash/toast',['key'=>$k,'text'=>$v]);
    }
    foreach (flash()->alerts as $k => $v) {
        echo $this->include('helper/flash/alert',['key'=>$k,'text'=>$v]);
    }
    echo "</div>";
}