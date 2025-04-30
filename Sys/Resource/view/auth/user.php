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
$role_list = [
    ['id'=>'user','val'=>'User'],
    ['id'=>'admin','val'=>'Admin']
];

?>
<form class="form-horizontal" method="POST" action="/auth/user">
<div class="card">
    <h5 class="card-header">Modify</h5>    
        <div class="panel panel-default">
            <div class="panel-body text-left">
                <div class="form-group mt-3">
                    <label for="fullname" class="col-md-4 control-label">Fullname</label>
                    <div class="col-md-8">
                        <?=$this->include('helper/form/inputonly',['field'=>$user->fields('fullname'),'label'=>'Name','size'=>24]);?>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label for="email" class="col-md-4 control-label">Email</label>
                    <div class="col-md-8">
                        <?=$this->include('helper/form/inputonly',['field'=>$user->fields('email'),'label'=>'Email','size'=>24]);?>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label for="role" class="col-md-4 control-label">Role</label>
                    <div class="col-md-8">
                        <?=$this->include('helper/form/selectonly',['name'=> 'role','key'=>'id','val'=>'val','list'=>$role_list]);?>
                    </div>
                </div>
                <button class="btn btn-outline-secondary btn-sm ml-4" type="button" data-toggle="collapse" data-target="#collapsePassword" aria-expanded="false" aria-controls="collapsePassword">
                    Password
                </button>
                <div class="collapse" id="collapsePassword">
                    <div class="form-group mt-3">
                        <label for="password_new" class="col-md-4 control-label">Password</label>
                        <div class="col-md-8">
                            <?=$this->include('helper/form/inputonly',['name'=>'password_new','label'=>'New Password','size'=>24,'type'=>'password']);?>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label for="password_confirm" class="col-md-4 control-label">Password Confirm</label>
                        <div class="col-md-8">
                            <?=$this->include('helper/form/inputonly',['name'=>'password_confirm','label'=>'New Password','size'=>24,'type'=>'password']);?>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control" name="id" value="<?=$user->id;?>">
            </div>
            <button class="btn btn-primary float-right mb-3 mr-3" type="submit"> Save </button>
            <a class="btn btn-outline-primary float-right mb-3 mr-3" href="/auth/users">  Cancel  </a>
        </div>
</div>
</form>
