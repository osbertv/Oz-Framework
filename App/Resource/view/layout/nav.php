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
 */
?>
       <nav class="navbar navbar-expand-md navbar-dark bg-dark" role="navigation">
          <a class="navbar-brand" href="/"><?=APP_NAME?></a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarsDefault">
            <ul class="navbar-nav mr-auto">
              <?php if (session('Username')) { ?>  
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="dropdownNetwork" aria-haspopup="true" aria-expanded="false">Network</a>
                <div class="dropdown-menu" aria-labelledby="dropdownNetwork">
                  <a class="dropdown-item" href="#">Locations</a>
                  <a class="dropdown-item" href="/devices">Devices</a>
                  <div class="dropdown-submenu">
                     <a class="dropdown-item dropdown-toggle" data-toggle="dropdown" href="/tickets">Tickets</a>
                     <div class="dropdown-menu">
                         <a class="dropdown-item" href="/tickets">List</a>
                         <a class="dropdown-item" href="/tickets/my_tickets">My Tickets</a>
                         <a class="dropdown-item" href="/tickets/create">New Ticket</a>
                         <a class="dropdown-item" href="/tickets/import">Import</a>
                         <a class="dropdown-item" href="/tickets/dict_sla_report">Reports</a>
                     </div>
                  </div>
                  <div class="dropdown-submenu">
                     <a class="dropdown-item dropdown-toggle" data-toggle="dropdown" href="#">Meraki</a>
                     <div class="dropdown-menu">
                         <a class="dropdown-item" href="/meraki/dashboard">Dashboard</a>
                         <a class="dropdown-item" href="/meraki/alarm_list">All Alarms</a>
                         <a class="dropdown-item" href="/meraki/alarms">Current Alarms</a>
                         <a class="dropdown-item" href="/meraki/restoredalarms">Restored Alarms</a>
                     </div>
                  </div>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="" id="dropdownAccounts" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Accounts</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                  <a class="dropdown-item" href="/accounts">Subscribers</a>
                  <a class="dropdown-item" href="/serviceorders">Service Orders</a>
                  <a class="dropdown-item" href="#">Purchases</a>
                  <a class="dropdown-item" href="#">Billing</a>
                </div>
              </li>
              <?php } ?>
            </ul>
            <ul class="navbar-nav navbar-right ml-auto">
                <!-- Authentication Links -->
                <?php if ( !session('Username') ) { ?>  
                <li class="nav-item"><a class="nav-link" href="/auth/login">Login</a></li>
                <?php } else { ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="" id="dropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">&nbsp;&nbsp; <?=session()->Fullname;?> &nbsp;&nbsp;</a>
                  <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="/auth/profile">Profile</a>
                    <?php if (session()->Role == 'admin'):?>
                    <a class="dropdown-item" href="/auth/users">Users</a>
                    <?php endif?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/auth/logout">Logout</a>
                  </div>
                </li>
                <?php } ?>
            </ul>
          </div>
        </nav>

