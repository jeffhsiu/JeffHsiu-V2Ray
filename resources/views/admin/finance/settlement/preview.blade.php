@extends('backpack::layout')

@section('header')
    <section class="content-header">
        <h1>
            <span class="text-capitalize">Settle</span>
            <small>settlement preview</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
            <li><a href="{{ backpack_url('finance/settlement')}}" class="text-capitalize">Settlements</a></li>
            <li class="active">List</li>
        </ol>
    </section>
@endsection

@section('content')
    <a href="{{ backpack_url('finance/settlement') }}" class="hidden-print"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>Settlement</span></a>

    <a href="javascript: window.print();" class="pull-right hidden-print"><i class="fa fa-print"></i></a>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <!-- Default box -->
            <div class="m-t-20">
                <div class="box box-success" style="border-top-width: 3px;">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            Revenues - <small>{{ $start_date }} ~ {{ $end_date }}</small>
                        </h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-border">
                        <table class="table table-striped table-hover responsive" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Distributor</th>
                                    <th>Price</th>
                                    <th>Commission</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as &$order)
                                    <tr>
                                        <td>{{ $order->customer->name }}</td>
                                        <td>{{ $order->distributor->name }}</td>
                                        <td>¥ {{ $order->price }}</td>
                                        <td>¥ {{ $order->commission }}</td>
                                        <td>¥ {{ $order->profit }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($orders) >= 5)
                            <p style="text-align:center;"><a href="{{ backpack_url('order/order?settlement_id=true&type=1') }}" target="_blank">... more</a></p>
                        @endif
                        <hr>
                        <div style="text-align: right;">
                            <span class="text-muted margin-r-5">Total revenue:</span>
                            <strong class="text-green">¥</strong>
                            <strong class="text-green" style="font-size: 3rem; margin-right: 20px">{{ $total_revenue }}</strong>
                        </div>
                    </div><!-- /.box-body -->
                </div>
            </div><!-- /.box -->

            <div class="box box-danger" style="border-top-width: 3px;">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        Costs - <small>{{ $start_date }} ~ {{ $end_date }}</small>
                    </h4>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body no-border">
                    <table class="table table-striped table-hover responsive" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Descript</th>
                                <th>Admin User</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($costs as $cost)
                                <tr>
                                    <td>{{ $cost->title }}</td>
                                    <td>{{ $cost->descript }}</td>
                                    <td>{{ $cost->user->name }}</td>
                                    <td>{{ $cost->date_notime }}</td>
                                    <td>¥ {{ $cost->amount }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(count($costs) >= 5)
                        <p style="text-align:center;"><a href="{{ backpack_url('finance/cost?settlement_id=true') }}" target="_blank">... more</a></p>
                    @endif
                    <hr>
                    <div style="text-align: right;">
                        <span class="text-muted margin-r-5">Total cost:</span>
                        <strong class="text-red">¥</strong>
                        <strong class="text-red" style="font-size: 3rem; margin-right: 20px">{{ $total_cost }}</strong>
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->

            <div class="box box-warning" style="border-top-width: 3px;">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        Settlement
                    </h4>
                </div>
                <div class="box-body no-border">
                    @foreach($admin_costs as $admin_cost)
                        <p class="text-center" style="font-size: 2.4rem">
                            Pay cost
                            <strong class="text-red m-l-5 m-r-5"><small>¥</small> {{ $admin_cost->amount + 0 }}</strong>
                            to <strong class="text-primary">{{ $admin_cost->user->name }}</strong>
                        </p>
                    @endforeach
                    @if(count($admin_costs))
                        <hr>
                    @endif
                    <p class="text-center" style="font-size: 2.4rem">Revenue - Cost = Net Profit</p>
                    <p class="text-center" style="font-size: 3rem">
                        <strong class="text-green"><small>¥</small> {{ $total_revenue + 0}}</strong> -
                        <strong class="text-red"><small>¥</small> {{ $total_cost + 0 }}</strong> =
                        <strong class="text-yellow"><small>¥</small> {{ $net_profit + 0 }}</strong>
                    </p>
                    <hr>
                    <div style="text-align: right;">
                        <span class="text-muted margin-r-5">Net Profit:</span>
                        <strong class="text-yellow">¥</strong>
                        <strong class="text-yellow" style="font-size: 3rem; margin-right: 20px">{{ number_format($net_profit, 2) }}</strong>
                    </div>
                    <button data-toggle="modal" data-target="#confirmModal"
                            class="btn btn-warning" style="display:block; margin: 0 auto;">
                        <span class="ladda-label">
                            <i class="fa fa-bank"></i> Settle Incomes
                        </span>
                    </button>

                </div><!-- /.box-body -->
            </div><!-- /.box -->

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Are you sure you want to settle the revenue?</h3>
                </div>
                <form action="{{ backpack_url('finance/settle') }}" method="post">
                    {{ csrf_field() }}
                    <div class="modal-body form-group">
                        <h4 class="padding-10">The order and cost will be settled.</h4>
                        <h4 class="padding-10">Settle remark:</h4>
                        <textarea class="form-control" name="remark" value=""
                        style="margin: 0 auto; width: 96%;"></textarea>
                    </div>
                    <div class="modal-footer">
                        <div id="loading" class="ld-over-full-inverse">
                            <div class="ld ld-ring ld-spin" style="font-size:3em"></div>
                        </div>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button id="modalSubmit" type="submit" class="btn btn-warning ">Settle</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>


@endsection

@section('after_styles')
    <!-- DATA TABLES -->
    <link href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.1/css/responsive.bootstrap.min.css">

    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/show.css') }}">
@endsection

@section('after_scripts')
    <!-- DATA TABLES SCRIPT -->
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.1/js/responsive.bootstrap.min.js"></script>

    <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/show.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(".table").DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: -1 }
                ]
            });
        });

    </script>
@endsection
