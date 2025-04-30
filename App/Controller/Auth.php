<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

class Auth extends \Oz\Controller {
    
    public $allowed_methods = ['login','logout'];
    
    function index() {
        return $this->profile();
    }
    
    function login() {
        if ( $this->request->param('username') && $this->request->param('password') ) {
            if (session()->login($this->request->param('username'),$this->request->param('password'))){
                $redirect = session('URL_Before_Login') ?? INDEXPATH;
                session()->URL_Before_Login = null;
                $this->redirect($redirect);
            } else {
                flash()->error('login',"<div class=\"form-group alert alert-warning\">Invalid username or password</div>");
            }
        }
        
        if (session()->is_loggedIn())
            $this->redirect(INDEXPATH);
        
        return $this->view('auth/login',[] ,false);
    }

    function logout()
    {
            session()->logout();
            $this->redirect(INDEXPATH);
    }
    
    function profile() {
        if (empty($id = session()->UserId))
                return  $this->view('auth/notallowed');
        $auth = new \App\Model\Auth($id);
        if ($this->request->param('id') == $id) {
            $fields['id'] = $this->request->param('id');
            if ($auth->fullname !== $this->request->param('fullname'))
                $fields['fullname'] = $this->request->param('fullname');
            if ($auth->email !== $this->request->param('email'))
                $fields['email'] = $this->request->param('email');
            if (! empty( $this->request->param('password_new')) ) {
                    if ($this->request->param('password_new') === $this->request->param('password_confirm')) {
                       $fields['password'] = $auth->hash($this->request->param('password_new')); 
                    } else {
                       flash()->alert('error',"Password mismatch. Not change."); 
                    }
            }
            //debug($auth->handle()->table());
            if ($auth->save($fields)) {
                flash()->toast('save',"Account updated");
            }
        }
        return  $this->view('auth/profile', ['user'=>$auth]);
    }
    
    
    function users() {
        if (!session()->is_admin()) {
            return  $this->view('auth/notallowed');
        }
        $page = $this->request->param('page',1);
        $limit = $this->request->param('limit',10);
        $search = $this->request->param('search');
        $model = new \App\Model\Auth();
        if (!empty($search)) {
            $model->where("username", "LIKE", "%$search%");
        }
        $data['rows'] = $model->paginate($page, $limit);
        $data['paginationHtml'] = $model->paginationHtml('/auth/users/');
        $data['title'] = 'Users';
        $data['table'] = $model->table();
        return  $this->view('auth/list', ['data' => $data ,'model'=>$model]);
    }

    function user()
    {
        if (!session()->is_admin()) {
            return  $this->view('auth/notallowed');
        }
        $id = $this->request->posted('id') ?? func_get_arg(0);
        if ($this->request->posted('id') == $id) {
            $model = new \App\Model\Auth($id);
            $this->request->_setPostParam('id',$id);
            $this->request->_setPostParam('password',$model->password);
            if ($model->validate_form($this->request->posted(), $fields)) {
                if (! empty( $this->request->posted('password_new')) ) {
                        if ($this->request->posted('password_new') === $this->request->posted('password_confirm')) {
                           $fields['password'] = $model->hash($this->request->posted('password_new')); 
                        } else {
                           flash()->alert('error',"Password mismatch. Not change."); 
                        }
                }
                //debug($auth->handle()->table());
                if ($model->save($fields)) {
                    flash()->toast('save',"Account updated");
                }
            }
        }
        $model = new \App\Model\Auth($id);
        return  $this->view('auth/user', ['user'=>$model]);
    }
    
    function adduser()
    {
        if (session()->Role !== 'admin') {
            return  $this->view('auth/notallowed');
        }
        $model = new \App\Model\Auth();
        if ($this->request->posted('email') && $this->request->posted('password')) {
            //$this->request->_setPostParam('username',$this->request->posted('email'));
            if ($model->validate_form($this->request->posted(), $fields)) {
                if ($this->request->posted('password') === $this->request->posted('password_confirm')) {
                    $fields['password'] = $model->hash($this->request->posted('password')); 
                    if ($id = $model->save($fields)) {
                        $model->find($id);
                        flash()->toast('save',"User created");
                        return  $this->view('auth/user', ['user'=> $model]);
                    } else {
                        flash()->alert('error',"There was an error saving record."); 
                        flash()->alert('error',"No User created."); 
                    }
                } else {
                   flash()->error('password',"Password mismatch. Not change."); 
                   flash()->alert('error',"Password mismatch. Not change."); 
                }
            } else {
                flash()->alert('error',"There was an error saving record."); 
                flash()->alert('error',"Form validation failed."); 
            }
        }
        return  $this->view('auth/useradd', ['user'=>$model,'title'=>'New User']);
    }

    
}