@extends('errors::layout')

@section('code', '419')
@section('title', 'Page Expired')

@section('image')
<div style="background-image: url('/svg/403.svg');" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection

@section('message', 'Sorry, your session has expired. Please refresh and try again.')
