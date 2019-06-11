@extends('backpack::layout')

@section('header')
    <section class="content-header">
        <h1>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>{!! $crud->getSubheading() ?? trans('backpack::crud.add').' '.$crud->entity_name !!}.</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
            <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
            <li class="active">{{ trans('backpack::crud.add') }}</li>
        </ol>
    </section>
@endsection

@section('content')
    @if ($crud->hasAccess('list'))
        <a href="{{ url($crud->route) }}" class="hidden-print"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a>
    @endif

    <div class="row m-t-20">
        <div class="{{ $crud->getCreateContentClass() }}">
            <!-- Default box -->

            @include('crud::inc.grouped_errors')

            <form method="post"
                  action="{{ url($crud->route) }}"
                  @if ($crud->hasUploadFields('create'))
                  enctype="multipart/form-data"
                    @endif
            >
                {!! csrf_field() !!}
                <div class="col-md-12">

                    <div class="row display-flex-wrap">
                        <!-- load the view from the application if it exists, otherwise load the one in the package -->
                        @if(view()->exists('vendor.backpack.crud.form_content'))
                            @include('vendor.backpack.crud.form_content', [ 'fields' => $crud->getFields('create'), 'action' => 'create' ])
                        @else
                            @include('crud::form_content', [ 'fields' => $crud->getFields('create'), 'action' => 'create' ])
                        @endif
                    </div><!-- /.box-body -->
                    <div class="">
                        <div id="saveActions" class="form-group">
                            @if ($from_server)
                                <input type="hidden" name="save_action" value="save_to_order">
                                <input type="hidden" name="server_id" value="{{ $server_id }}">
                                <input type="hidden" name="docker_name" value="{{ $docker_name }}">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-success">
                                        <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
                                        <span data-value="save_to_order">Save to order</span>
                                    </button>
                                </div>
                            @else
                                <input type="hidden" name="save_action" value="{{ $saveAction['active']['value'] }}">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-success">
                                        <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
                                        <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
                                    </button>

                                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aira-expanded="false">
                                        <span class="caret"></span>
                                        <span class="sr-only">&#x25BC;</span>
                                    </button>

                                    <ul class="dropdown-menu">
                                        @foreach( $saveAction['options'] as $value => $label)
                                            <li><a href="javascript:void(0);" data-value="{{ $value }}">{{ $label }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <a href="{{ $crud->hasAccess('list') ? url($crud->route) : url()->previous() }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
                            @if ($from_server)
                            <a href="{{ backpack_url('order/order/create'.'?server_id='.$server_id.'&docker_name='.$docker_name) }}" class="btn btn-warning" style="float: right;">
                                <span class="fa fa-send-o"></span> &nbsp; Skip
                            </a>
                            @endif
                        </div>
                    </div><!-- /.box-footer-->
                </div><!-- /.box -->
            </form>
        </div>
    </div>

@endsection
