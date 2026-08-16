@if (session('succes'))
    <div class="alerte alerte--succes">{{ session('succes') }}</div>
@endif

@if (session('erreur'))
    <div class="alerte alerte--erreur">{{ session('erreur') }}</div>
@endif

@if ($errors->any())
    <div class="alerte alerte--erreur">
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
