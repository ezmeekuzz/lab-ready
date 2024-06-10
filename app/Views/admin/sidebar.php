<aside class="app-navbar">
    <div class="sidebar-nav scrollbar scroll_dark">
        <ul class="metismenu" id="sidebarNav">
            <li class="nav-static-title">Personal</li>
            <!--<li <?php if($currentpage == 'dashboard') { echo 'class="active"'; } ?>>
                <a href="/dashboard" aria-expanded="false">
                    <i class="nav-icon fa fa-dashboard"></i>
                    <span class="nav-title">Dashboard</span>
                </a>
            </li>-->
            <li <?php if($currentpage == 'sendquotation') { echo 'class="active"'; } ?>>
                <a href="/send-quotation" aria-expanded="false">
                    <i class="nav-icon fa fa-paper-plane"></i>
                    <span class="nav-title">Send Quotation</span>
                </a>
            </li>
            <li <?php if($currentpage == 'quotationmasterlist') { echo 'class="active"'; } ?>>
                <a href="/quotation-masterlist" aria-expanded="false">
                    <i class="nav-icon fa fa-file-text-o"></i>
                    <span class="nav-title">Quotation Masterlist</span>
                </a>
            </li>
            <li <?php if($currentpage == 'requestquotationlist') { echo 'class="active"'; } ?>>
                <a href="/request-quotation-masterlist" aria-expanded="false">
                    <i class="nav-icon fa fa-file-pdf-o"></i>
                    <span class="nav-title">Request Quotation List</span>
                </a>
            </li>
            <li <?php if($currentpage == 'subscribersmasterlist') { echo 'class="active"'; } ?>>
                <a href="/subscribers-masterlist" aria-expanded="false">
                    <i class="nav-icon fa fa-users"></i>
                    <span class="nav-title">Subscribers Masterlist</span>
                </a>
            </li>
            <li <?php if($currentpage == 'sendnewsletter') { echo 'class="active"'; } ?>>
                <a href="/send-newsletter" aria-expanded="false">
                    <i class="nav-icon fa fa-newspaper-o"></i>
                    <span class="nav-title">Send Newsletter</span>
                </a>
            </li>
            <li class="nav-static-title">Users</li>
            <li <?php if($currentpage == 'adduser' || $currentpage == 'usermasterlist') { echo 'class="active"'; } ?>>
                <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                    <i class="nav-icon ti ti-user"></i>
                    <span class="nav-title">Users</span>
                </a>
                <ul aria-expanded="false">
                    <li <?php if($currentpage == 'adduser') { echo 'class="active"'; } ?>> <a href='/add-user'>Add User</a> </li>
                    <li <?php if($currentpage == 'usermasterlist') { echo 'class="active"'; } ?>> <a href='/user-masterlist'>User Masterlist</a> </li>
                </ul>
            </li>
            <li class="nav-static-title">Logout</li>
            <li>
                <a href="/admin/logout" aria-expanded="false">
                    <i class="nav-icon ti ti-power-off"></i>
                    <span class="nav-title">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>