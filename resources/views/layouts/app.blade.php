<!DOCTYPE html>
<html lang="fr" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Data Center Resource Manager')</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashRespo.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @stack('styles')
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-content container">
            <!-- Logo -->
            <div class="logo">
                <a href="{{ url('/') }}" class="logo-link">
                    <div class="logo-icon">
                        <i class="fa-duotone fa-solid fa-house"></i>
                    </div>
                    <h1><span class="my">My</span> <span class="dc">DataCentre</span> </h1>
                </a>
            </div>

            <!-- Navigation -->
            <nav>
                <ul class="nav-menu">
                    <!-- Accueil pour tous -->
                    <li><a href="{{ url('/') }}" class="nav-link"><i class="fas fa-home"></i> Accueil</a></li>

                    @guest
                        <!-- Menu pour les invités -->
                        <li><a href="{{ route('resources.index') }}" class="nav-link"><i class="fas fa-list"></i>
                                Ressources</a></li>
                        <li><a href="{{ route('login') }}" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i>
                                Connexion</a></li>
                        <li><a href="{{ route('register') }}" class="btn btn-outline"><i class="fas fa-user-plus"></i>
                                S'inscrire</a></li>
                    @else
                        <!-- Menu selon le rôle -->
                        @if(auth()->user()->isAdmin())
                            <li><a href="{{ route('dashboard.admin') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i>
                                    Tableau de bord</a></li>
                            <li><a href="{{ route('users.index') }}" class="nav-link"><i class="fas fa-users"></i> User</a></li>
                            <li><a href="{{ route('maintenance.index') }}" class="nav-link"><i class="fas fa-tools"></i>
                                    Maintenance</a></li>
                        @elseif(auth()->user()->isResponsable())
                            <li><a href="{{ route('responsable.dashboard') }}" class="nav-link"><i
                                        class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        @else
                            <li><a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i>
                                    Tableau de bord</a></li>
                        @endif

                        <!-- Menu commun pour tous les utilisateurs connectés -->
                        <li><a href="{{ route('resources.index') }}" class="nav-link"><i class="fas fa-server"></i>
                                Ressources</a></li>
                        <li><a href="{{ route('reservations.index') }}" class="nav-link"><i class="fas fa-calendar-alt"></i>
                                Réserv</a></li>
                    @endguest
                </ul>
            </nav>

            <!-- User Menu -->
            <div class="user-menu">
                @auth
                    <!-- Notifications Dropdown -->
                    <div class="dropdown notifications-dropdown">
                        <button class="notification-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge badge badge-danger" style="display: none;">0</span>
                        </button>
                        <div class="dropdown-menu notifications-menu">
                            <div class="dropdown-header">
                                <h5>Notifications</h5>
                                <a class="btn" href="{{ route('notifications.index') }}">Voir tout</a>
                            </div>
                            <div class="notifications-list">
                                <!-- Les notifications seront chargées via JavaScript -->
                                <div class="notification-item">
                                    <div class="notification-loading">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown profile-dropdown">
                        <button class="user-profile-btn">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name">{{ auth()->user()->prenom }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu profile-menu">
                            <div class="user-info">
                                <div class="user-avatar-lg">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <span class="user-fullname">{{ auth()->user()->prenom }}
                                        {{ auth()->user()->nom }}</span>
                                    <span class="user-email">{{ auth()->user()->email }}</span>
                                    <span class="user-role">
                                        @if(auth()->user()->isAdmin())
                                            <span class="badge badge-success">Admin</span>
                                        @elseif(auth()->user()->isResponsable())
                                            <span class="badge badge-warning">Responsable</span>
                                        @else
                                            <span class="badge badge-info">Utilisateur</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('profile', ['id' => 'me']) }}" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> Mon profil
                            </a>
                            <a href="{{ route('notifications.index') }}" class="dropdown-item">
                                <i class="fas fa-bell"></i> Notifications
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('categories.index') }}" class="dropdown-item">
                                    <i class="fas fa-folder"></i> Catégories
                                </a>
                                <a href="{{ route('history.index') }}" class="dropdown-item">
                                    <i class="fas fa-history"></i> Historique
                                </a>
                            @endif
                            @if(auth()->user()->isResponsable())
                                <a href="{{ route('reported-messages.index') }}" class="dropdown-item">
                                    <i class="fas fa-flag"></i> Messages signalés
                                </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                @csrf
                                <button type="submit" class="dropdown-item" style="font-weight: bold">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Content -->
        <div class="container">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success fade-in flash-message">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger fade-in flash-message">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning fade-in flash-message">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info fade-in flash-message">
                    <i class="fas fa-info-circle"></i> {{ session('info') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <!-- JavaScript Files -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script src="{{ asset('js/reservations.js') }}"></script>

    <!-- Additional Scripts -->
    @stack('scripts')

    <!-- Inline Scripts -->
    <script>
        // Initialiser l'application
        document.addEventListener('DOMContentLoaded', function () {
            // Basculer le menu mobile
            const menuToggle = document.querySelector('.menu-toggle');
            const mobileNav = document.querySelector('.mobile-nav');

            if (menuToggle && mobileNav) {
                menuToggle.addEventListener('click', function () {
                    mobileNav.classList.toggle('active');
                });

                // Fermer le menu mobile en cliquant à l'extérieur
                document.addEventListener('click', function (event) {
                    if (!menuToggle.contains(event.target) && !mobileNav.contains(event.target)) {
                        mobileNav.classList.remove('active');
                    }
                });
            }

            // Gérer les menus déroulants
            const dropdowns = document.querySelectorAll('.dropdown');

            dropdowns.forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('.dropdown-menu');

                if (button && menu) {
                    button.addEventListener('click', function (e) {
                        e.stopPropagation();

                        // Fermer tous les autres menus
                        dropdowns.forEach(other => {
                            if (other !== dropdown) {
                                other.querySelector('.dropdown-menu')?.classList.remove('show');
                            }
                        });

                        menu.classList.toggle('show');
                    });

                    // Fermer le menu en cliquant ailleurs
                    document.addEventListener('click', function () {
                        menu.classList.remove('show');
                    });

                    menu.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                }
            });

            // Charger les notifications
            loadNotifications();

            // Fonction pour charger les notifications
            function loadNotifications() {
                @auth
                    fetch('{{ route("notifications.unread-count") }}')
                        .then(response => response.json())
                        .then(data => {
                            const badges = document.querySelectorAll('.notification-badge');
                            badges.forEach(badge => {
                                if (data.count > 0) {
                                    badge.textContent = data.count;
                                    badge.style.display = 'inline-block';
                                } else {
                                    badge.style.display = 'none';
                                }
                            });

                            // Charger les dernières notifications
                            fetch('{{ route("notifications.latest") }}')
                                .then(res => res.json())
                                .then(data => {
                                    const list = document.querySelector('.notifications-list');
                                    list.innerHTML = '';
                                    if (data.length) {
                                        data.forEach(n => {
                                            const item = document.createElement('div');
                                            item.className = `notification-item ${n.est_lu ? 'read' : 'unread'}`;
                                            item.innerHTML = `
                                                                    <div class="notification-title">${n.titre}</div>
                                                                    <div class="notification-message">${n.message}</div>
                                                                    <div class="notification-time">${new Date(n.created_at).toLocaleString()}</div>

                                                                `;
                                            list.appendChild(item);
                                        });
                                    } else {
                                        list.innerHTML = `<div class="notification-empty">Aucune notification</div>`;
                                    }
                                });

                        }).catch(error => console.error('Erreur:', error));
                @endauth
            }

            // Mettre à jour les notifications toutes les 30 secondes
            setInterval(loadNotifications, 30000);
        });

        // Flash messages JS
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.flash-message');

            alerts.forEach(alert => {
                alert.style.position = 'fixed';
                alert.style.top = '20px';
                alert.style.right = '20px';
                alert.style.zIndex = '9999';
                alert.style.opacity = '1';
                alert.style.transform = 'translateY(0)';

                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            });
        });

    </script>
</body>

</html>