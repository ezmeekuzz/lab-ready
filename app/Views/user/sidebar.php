<aside class="app-navbar">
    <div class="sidebar-nav scrollbar scroll_dark">
        <ul class="metismenu" id="sidebarNav">
            <li class="nav-static-title">Personal</li>
            <li <?php if($currentpage == 'quotations') { echo 'class="active"'; } ?>>
                <a href="/quotations" aria-expanded="false">
                    <i class="nav-icon fa fa-file-text-o"></i>
                    <span class="nav-title">Quotations</span>
                </a>
            </li>
            <li <?php if($currentpage == 'requestquotation') { echo 'class="active"'; } ?>>
                <a href="/request-quotation" aria-expanded="false">
                    <i class="nav-icon fa fa-file-pdf-o"></i>
                    <span class="nav-title">Request Quotation</span>
                </a>
            </li>
            <li <?php if($currentpage == 'requestquotationlist') { echo 'class="active"'; } ?>>
                <a href="/request-quotation-list" aria-expanded="false">
                    <i class="nav-icon fa fa-archive"></i>
                    <span class="nav-title">Request Quotation List</span>
                </a>
            </li>
            <li class="nav-static-title">Logout</li>
            <li>
                <a href="/user/logout" aria-expanded="false">
                    <i class="nav-icon ti ti-power-off"></i>
                    <span class="nav-title">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>