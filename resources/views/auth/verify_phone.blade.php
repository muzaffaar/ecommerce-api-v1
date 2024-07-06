@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Phone Number') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A verification code has been sent to your email address.') }}
                        </div>
                    @endif
                    @if (session('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif
                    <form class="d-inline" method="POST" action="{{ route('phone.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="verification_code" class="form-control">
                            @error('verification_code')
                                <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-info py-1 m-0 align-baseline">{{ __('Verify') }}</button>
                    </form>

                    <hr>

                    {{ __('Before proceeding, please check your phone for a verification link.') }}
                    {{ __('If you did not receive the verification code') }},
                    
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
