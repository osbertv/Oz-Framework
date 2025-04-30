<?php

/* 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		
 @filesource            App/*.php
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 * 
 * toast template called by flash helper
 * 
 */
?>
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="4000">
              <div class="toast-header">
                <strong class="mr-auto"><?=$key?></strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="toast-body">
                <?= (is_array($text)) ? implode("<br/>", $text) : $text ?>
              </div>
            </div>
