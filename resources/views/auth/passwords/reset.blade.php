@extends('auth.layout')

@section('content')

<div class="container">
    <div class="row vh-100 ">
      <div class="col-12 align-self-center">
        <div class="auth-page">
          <div class="card auth-card shadow-lg">
            <div class="card-body p-0">
              <div class="text-center" style="color:red;width:100%"> </div>
              <div class="col-lg-12">
                <div class="row" style="box-shadow: 0.5px 0.1rem 0.5rem rgba(252, 212, 212, 0.5);">
                  <div class="col-lg-12 pt-3 text-center p-3"> <em> Welcome to {{config('app.name')}} </em> </div>

                  <div class="col-lg-12 mt-4 pt-5 p-3">
                      <div class="card">
                          <div class="card-body p-0 auth-header-box">
                              <div class="text-center p-3">
                                  <p class="text-muted mt-2 pt-2 mb-0"> Reset New Password </p>
                              </div>
                          </div>
                      </div>
                    <div class="auth-logo-box p-2 pt-2 pb-2">
                      <a href="{{ route('login') }}" class="logo logo-admin"><img src="{{asset('assets/images/logo.png')}}" width="85%" height="auto" alt="logo" style="border-radius:5px;" class="auth-logo"></a>
                    </div>

                    @if (Session::has('failed'))
                      <p class="text-center" style="color:red">{{ Session::get('failed') }}</p>
                    @endif

                  @if (session('status'))
                      <p class="text-center" style="color: #41B680"><strong>Your Password Reset successfully</strong></p>
                  @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <label for="email"><strong>Email</strong></label>

                            <div class="input-group mb-1">
                              <span class="auth-form-icon mt-2 pr-1">
                                <i class="dripicons-user"></i>
                              </span>

                              <input id="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror" name="email"
                                                value="{{$email}}" autocomplete="email" readonly>

                              @error('email')
                                <span class="invalid-feedback" role="alert">
                                  <strong>{{ $message }}</strong>
                                </span>
                              @enderror

                            </div>
                          </div>


                          <div class="form-group">
                            <label for="password"><strong>Password</strong></label>

                            <div class="input-group mb-0">
                              <span class="auth-form-icon mt-2 pr-1">
                                <i class="dripicons-lock"></i>
                              </span>

                              <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter Password" required autocomplete="current-password">

                              @error('password')
                                <span class="invalid-feedback" role="alert">
                                  <strong>{{ $message }}</strong>
                                </span>
                              @enderror
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="password-confirm"><strong>Confirm Password</strong></label>

                            <div class="input-group mb-0">
                              <span class="auth-form-icon mt-2 pr-1">
                                <i class="dripicons-lock"></i>
                              </span>

                              <input id="password-confirm" type="password" class="form-control @error('password-confirm') is-invalid @enderror" name="password_confirmation" placeholder="Enter Password" required autocomplete="new-password">

                              @error('password-confirm')
                                <span class="invalid-feedback" role="alert">
                                  <strong>{{ $message }}</strong>
                                </span>
                              @enderror
                            </div>
                          </div>
                        <div class="row mb-0">
                            <div class="col-12 mt-2">
                                <button style="background-color: #41B680 !important;border-color: #41B680 !important;" class="btn btn-gradient-success btn-round btn-block waves-effect waves-light" type="submit">
                                    {{ __('Reset Password') }}
                                    <i class="fas fa-sign-in-alt ml-1"></i>
                                  </button>
                            </div>
                        </div>
                        <div class="row mb-0 text-center">
                            <div class="col-12 mt-2">
                                <a class="btn p-0 text-center" href="{{ route('login') }}"
                                    style="font-size: 12px !important; font-weight: normal;">
                                    {{ __('Back to Login') }}
                                </a>
                            </div>
                        </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
