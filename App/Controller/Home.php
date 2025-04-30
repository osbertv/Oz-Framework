<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

class Home extends Controller {
    
    public $allowed_methods = ['index'];

    function index() {
	return $this->view('home/index');
    }

}