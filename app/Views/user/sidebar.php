<aside class="app-navbar">
    <div class="sidebar-nav scrollbar scroll_dark">
        <ul class="metismenu" id="sidebarNav">
            <li <?php if($currentpage == 'quotations') { echo 'class="active"'; } ?>>
                <a href="/quotations" aria-expanded="false">
                    <i class="nav-icon fa fa-file-text-o"></i>
                    <span class="nav-title">Quotes</span>
                </a>
            </li>
            <li <?php if($currentpage == 'requestquotation') { echo 'class="active"'; } ?>>
                <a href="/request-quotation" aria-expanded="false">
                    <i class="nav-icon fa fa-file-pdf-o"></i>
                    <span class="nav-title">Request New Quote</span>
                </a>
            </li>
            <li <?php if($currentpage == 'requestquotationlist') { echo 'class="active"'; } ?>>
                <a href="/request-quotation-list" aria-expanded="false">
                    <i class="nav-icon fa fa-archive"></i>
                    <span class="nav-title">Requested Quotes List</span>
                </a>
            </li>
            <li <?php if($currentpage == 'userinfo') { echo 'class="active"'; } ?>>
                <a href="/user-info" aria-expanded="false">
                    <i class="nav-icon fa fa-user"></i>
                    <span class="nav-title">User Info</span>
                </a>
            </li>
            <li>
                <a href="/user/logout" aria-expanded="false">
                    <i class="nav-icon ti ti-power-off"></i>
                    <span class="nav-title">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>