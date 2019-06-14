<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li><a href="{{ backpack_url('dashboard') }}"><i class="fa fa-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
@can('file-manager')
<li><a href="{{ backpack_url('elfinder') }}"><i class="fa fa-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
@endcan

@can('admin')
<!-- Users, Roles Permissions -->
<li class="treeview">
    <a href="#"><i class="fa fa-group"></i> <span>Admin</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        @can('admin-users')
        <li><a href="{{ backpack_url('user') }}"><i class="fa fa-user"></i> <span>Users</span></a></li>
        @endcan
        @can('admin-roles')
        <li><a href="{{ backpack_url('role') }}"><i class="fa fa-group"></i> <span>Roles</span></a></li>
        @endcan
        @can('admin-permissions')
        <li><a href="{{ backpack_url('permission') }}"><i class="fa fa-key"></i> <span>Permissions</span></a></li>
        @endcan
    </ul>
</li>
@endcan

@can('vps')
<!-- VPS -->
<li class="treeview">
    <a href="#"><i class="fa fa-globe"></i> <span>VPS</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        @can('vps-accounts')
        <li><a href="{{ backpack_url('vps/account') }}"><i class="fa fa-user"></i> <span>Accounts</span></a></li>
        @endcan
        @can('vps-servers')
        <li><a href="{{ backpack_url('vps/server') }}"><i class="fa fa-server"></i> <span>Servers</span></a></li>
        @endcan
        @can('vps-server-list')
        <li><a href="{{ backpack_url('vps/server/order-list') }}"><i class="fa fa-list-ul"></i> <span>Server List</span></a></li>
        @endcan
    </ul>
</li>
@endcan

@can('order')
<!-- Order -->
<li class="treeview">
    <a href="#"><i class="fa fa-file-text-o"></i> <span>Order</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        @can('vps-distributors')
        <li><a href="{{ backpack_url('order/distributor') }}"><i class="fa fa-user-secret"></i> <span>Distributors</span></a></li>
        @endcan
        @can('order-customers')
        <li><a href="{{ backpack_url('order/customer') }}"><i class="fa fa-users"></i> <span>Customers</span></a></li>
        @endcan
        @can('order-orders')
        <li><a href="{{ backpack_url('order/order') }}"><i class="fa fa-file-text-o"></i> <span>Orders</span></a></li>
        @endcan
    </ul>
</li>
@endcan

@can('logs')
<li><a href='{{ url(config('backpack.base.route_prefix', 'admin').'/log') }}'><i class='fa fa-terminal'></i> <span>Logs</span></a></li>
@endcan