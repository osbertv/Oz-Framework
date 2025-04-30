<?php
/* 
 +-----------------------------------------------------------+ 
    Oz Framework - Simple, Fast. 
 +-----------------------------------------------------------+ 
 @Copyright		(c) 2012 - present, Osbert Villanueva 
 @Package		
 @filesource            
 +-----------------------------------------------------------+ 
 @Author		Osbert Villanueva <osbertv@gmail.com> 
 +-----------------------------------------------------------+ 
 */
$this->section('content');
?>
<form class="form-horizontal" method="POST" action="/auth/profile">
<div class="card">
    <h5 class="card-header">Profile Edit</h5>    
        <div class="panel panel-default">
            <div class="panel-body text-left">
                <?=$this->include('helper/form/input',['field'=>$user->fields('fullname'),'label'=>'Name','size'=>24]);?>
                <?=$this->include('helper/form/input',['field'=>$user->fields('email'),'label'=>'Email','size'=>24]);?>
                <?=$this->include('helper/form/input',['name'=>'password_new','label'=>'New Password','size'=>24,'type'=>'password','after'=>'Leave empty to ignore change']);?>
                <?=$this->include('helper/form/input',['name'=>'password_confirm','label'=>'Confirm New Password','size'=>24,'type'=>'password']);?>
                <input type="hidden" class="form-control" name="id" value="<?=$user->id;?>">
            </div>
            <button class="btn btn-primary float-right mb-3 mr-3 disabled" type="submit"> Save </button>
        </div>
</div>
</form>
