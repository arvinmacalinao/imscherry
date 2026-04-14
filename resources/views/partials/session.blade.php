<div class="container-xl px-4 mt-n4">

    {{-- SUCCESS --}}
    @if(session('success'))
    <div class="alert alert-success alert-icon" role="alert">
        <button class="btn-close" type="button" data-bs-dismiss="alert"></button>

        <div class="alert-icon-aside">
            <i class="far fa-flag"></i>
        </div>

        <div class="alert-icon-content">
            {{ session('success') }}
        </div>
    </div>
    @endif


    {{-- ERROR --}}
    @if(session('error'))
    <div class="alert alert-danger alert-icon" role="alert">
        <button class="btn-close" type="button" data-bs-dismiss="alert"></button>

        <div class="alert-icon-aside">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <div class="alert-icon-content">
            {{ session('error') }}
        </div>
    </div>
    @endif

</div>