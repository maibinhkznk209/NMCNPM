@extends('layouts.app')

@section('floating-shapes')
  @include('components.floating-shapes')
@endsection

<style>
.bg-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 20s infinite linear;
}

.shape:nth-child(1) {
    width: 80px;
    height: 80px;
    top: 10%;
    left: 10%;
    animation-delay: -5s;
}

.shape:nth-child(2) {
    width: 120px;
    height: 120px;
    top: 70%;
    left: 80%;
    animation-delay: -10s;
}

.shape:nth-child(3) {
    width: 60px;
    height: 60px;
    top: 40%;
    left: 5%;
    animation-delay: -15s;
}

.shape:nth-child(4) {
    width: 100px;
    height: 100px;
    top: 20%;
    left: 85%;
    animation-delay: -2s;
}
</style>