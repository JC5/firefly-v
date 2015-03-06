@extends('layouts.default')
@section('content')
{!! Breadcrumbs::renderIfExists(Route::getCurrentRoute()->getName()) !!}

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h3>Active reminders</h3>
    </div>
</div>

@include('list.reminders',['reminders' => $active])

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h3>Dismissed reminders</h3>
    </div>
</div>

@include('list.reminders',['reminders' => $dismissed])

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h3>Expired reminders</h3>
    </div>
</div>

@include('list.reminders',['reminders' => $expired])

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h3>Inactive reminders</h3>
    </div>
</div>

@include('list.reminders',['reminders' => $inactive])




@stop
