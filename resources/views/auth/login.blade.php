@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <a class="btn btn-block btn-danger btn-sm" href="{{ route("sso.login") }}">Sign In with SSO</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
