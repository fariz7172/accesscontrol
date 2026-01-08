@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif
<style>
    .btn-no-bg {
        background: none;
        border: none;
        color: #dc3545;
        /* Warna ikon (merah) */
        padding: 0;
        cursor: pointer;
    }

    .btn-no-bg:hover {
        color: #a71d2a;
        /* Warna ikon saat hover (merah tua) */
    }
</style>