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
<main role="main" class="container-fluid mt-2 mb-2">
    <div class="card">
        <h5 class="card-header"> Users 
        <form class="form-inline mr-2 float-right">
            <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search" name="search">
            <button class="btn btn-outline-success" type="submit"> Search </button>
            <a  class="btn btn-outline-success ml-sm-2" href="/auth/adduser"> Add User </a>
        </form></h5>
        <div class="card-body">
                <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Email</th>
                        <th scope="col">Fullname</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($data['rows'])) { ?>
                        <tr><th scope="row" colspan="3">
                            <div><strong><i>No record found</i></strong></div>
                        </th></tr>
                      <?php } else { foreach ($data['rows'] as $row) {?>
                        <tr>
                          <th scope="row"><a href="/auth/user/<?=$row->id?>"><?=$row->username?></a></th>
                          <td><?=$row->email?></td>
                          <td><?=$row->fullname?></td>
                        </tr>
                      <?php }}?>
                    </tbody>
                </table>
                <?php if ($data['rows']) echo $data['paginationHtml']; ?>
        </div>
    </div>
</main>

<div class="modal" id="viewUser" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-body" style="margin:1px">
            <div id="viewUserData">
                
            </div>
        </div>
    </div>
  </div>
</div>

<script>
    $('#viewUser').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var link = button.data('link');
        $('#viewUserData').load(link);
    })
</script>