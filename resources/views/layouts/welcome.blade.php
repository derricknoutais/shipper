<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css"
        integrity="sha384-KA6wR/X5RY4zFAHpv/CnoG2UW1uogYfdnP67Uv7eULvTveboZJg0qUpmJZb5VqzN" crossorigin="anonymous">

</head>

<body>
    {{-- Navigation --}}
    <div id="app" class="tw-h-screen tw-flex tw-flex-col">

        <header class="tw-container-fluid tw-flex tw-bg-gray-900 tw-text-white tw-justify-between tw-p-5">
            <div>
                <a href="/">
                    SHIPPER POINT FINAL
                </a>
            </div>
            @auth
                <nav>
                    <a class="tw-mx-3" href="/template">Templates</a>
                    <a class="tw-mx-3" href="/commande">Commandes</a>
                    <a class="tw-mx-3" href="/product">Produits</a>
                    <a class="tw-mx-3" href="/fournisseur">Fournisseur</a>
                    <a class="tw-mx-3" href="/handle">Traductions</a>
                    {{-- <a class="tw-mx-3" href="/inventory-count">Inventory Count</a> --}}
                </nav>
            @else
                <nav>
                    <a class="tw-mx-3" href="/login">Se Connecter</a>
                    <a class="tw-mx-3" href="/register">Créer un compte</a>
                </nav>
            @endauth
        </header>

        <main class="tw-container-fluid tw-flex-1 tw-flex-col tw-justify-center tw-items-center">
            @yield('content')
        </main>

        <footer class="tw-container-fluid tw-bg-gray-900 tw-text-white tw-p-5 tw-bottom-0">
            <p>Services Tous Azimuts</p>
            <div class="tw-flex tw-items-center">
                <img src="https://cdn.icon-icons.com/icons2/159/PNG/256/facebook_22567.png" class="tw-w-5 tw-m-3" />
                <img src="https://upload.wikimedia.org/wikipedia/fr/thumb/c/c8/Twitter_Bird.svg/300px-Twitter_Bird.svg.png"
                    class="tw-w-5 tw-m-3" />
                <img src="https://cdn.icon-icons.com/icons2/914/PNG/512/Instagram_Color_icon-icons.com_71811.png"
                    class="tw-w-6 tw-m-3" />
            </div>
        </footer>

    </div>


    <script src="/js/app.js"></script>
</body>

</html>
