<div class="w-100 bg-light navbar-content">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="{{ url('/search') }}">
                <img src="{{ asset('/images/logo-ui42.png') }}" alt="Logo">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                </ul>
                <ul class="navbar-nav mr-end d-flex align-items-center">
                    <li>
                        <a href="#" class="mr-3"><b>Kontakty a čísla na oddelenia</b></a>
                    </li>
                    <li class="nav-item dropdown mr-3">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLanguage" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            EN
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownLanguage">
                            <a class="dropdown-item" href="#">EN</a>
                            <a class="dropdown-item" href="#">SK</a>
                        </div>
                    </li>
                    <li>
                        <input class="form-control" type="search" placeholder="Search" aria-label="Search">
                    </li>
                    <li>
                        <button class="btn btn-success my-2 my-sm-0 pl-4 pr-4 ml-3" type="submit">Prihlásenie</button>
                    </li>
                </ul>
            </div>
        </nav>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active pr-3">
                        <a class="nav-link" href="/search">Vyhľadať</a>
                    </li>
                    <li class="nav-item pr-3">
                        <a class="nav-link" href="#">O nás</a>
                    </li>
                    <li class="nav-item pr-3">
                        <a class="nav-link" href="#">Zoznam miest</a>
                    </li>
                    <li class="nav-item pr-3">
                        <a class="nav-link" href="#">Inšpekcia</a>
                    </li>
                    <li class="nav-item pr-3">
                        <a class="nav-link" href="#">Kontakt</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
