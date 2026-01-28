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
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
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
                        <i class="fas fa-server"></i>
                    </div>
                    <h1>Data Center Manager</h1>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav>
                <ul class="nav-menu">
                    <!-- Accueil pour tous -->
                    <li><a href="{{ url('/') }}" class="nav-link"><i class="fas fa-home"></i> Accueil</a></li>
                    
                    @guest
                        <!-- Menu pour les invités -->
                        <li><a href="{{ route('resources.index') }}" class="nav-link"><i class="fas fa-list"></i> Ressources</a></li>
                        <li><a href="{{ route('login') }}" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                        <li><a href="{{ route('register') }}" class="btn btn-outline"><i class="fas fa-user-plus"></i> S'inscrire</a></li>
                    @else
                        <!-- Menu selon le rôle -->
                        @if(auth()->user()->isAdmin())
                            <li><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="{{ route('admin.users') }}" class="nav-link"><i class="fas fa-users"></i> User</a></li>
                            <li><a href="{{ route('maintenance.index') }}" class="nav-link"><i class="fas fa-tools"></i> Maintenance</a></li>
                        @elseif(auth()->user()->isResponsable())
                            <li><a href="{{ route('responsable.dashboard') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="{{ route('responsable.resources') }}" class="nav-link"><i class="fas fa-network-wired"></i> Mes Ressources</a></li>
                            <li><a href="{{ route('responsable.reservations') }}" class="nav-link"><i class="fas fa-check-circle"></i> Approbations</a></li>
                        @else
                            <li><a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        @endif
                        
                        <!-- Menu commun pour tous les utilisateurs connectés -->
                        <li><a href="{{ route('resources.index') }}" class="nav-link"><i class="fas fa-server"></i> Ressources</a></li>
                        <li><a href="{{ route('reservations.index') }}" class="nav-link"><i class="fas fa-calendar-alt"></i> Réserv</a></li>
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
                            <div class="dropdown-footer">
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline">Toutes les notifications</a>
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
                                    <span class="user-fullname">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span>
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
                                <a href="{{ route('admin.categories') }}" class="dropdown-item">
                                    <i class="fas fa-folder"></i> Catégories
                                </a>
                                <a href="{{ route('history.index') }}" class="dropdown-item">
                                    <i class="fas fa-history"></i> Historique
                                </a>
                            @endif
                            @if(auth()->user()->isResponsable())
                                <a href="{{ route('responsable.reported-messages') }}" class="dropdown-item">
                                    <i class="fas fa-flag"></i> Messages signalés
                                </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                @csrf
                                <button type="submit" class="dropdown-item">
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

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <ul class="mobile-nav-menu">
            <li><a href="{{ url('/') }}"><i class="fas fa-home"></i> Accueil</a></li>
            @guest
                <li><a href="{{ route('resources.index') }}"><i class="fas fa-list"></i> Ressources</a></li>
                <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                <li><a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> S'inscrire</a></li>
            @else
                @if(auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Admin</a></li>
                    <li><a href="{{ route('admin.users') }}"><i class="fas fa-users"></i> Utilisateurs</a></li>
                @elseif(auth()->user()->isResponsable())
                    <li><a href="{{ route('responsable.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Responsable</a></li>
                    <li><a href="{{ route('responsable.resources') }}"><i class="fas fa-network-wired"></i> Mes Ressources</a></li>
                @else
                    <li><a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Tableau</a></li>
                @endif
                <li><a href="{{ route('resources.index') }}"><i class="fas fa-server"></i> Ressources</a></li>
                <li><a href="{{ route('reservations.index') }}"><i class="fas fa-calendar-alt"></i> Réservations</a></li>
                <li><a href="{{ route('profile', ['id' => 'me']) }}"><i class="fas fa-user"></i> Profil</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mobile-logout">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </button>
                    </form>
                </li>
            @endguest
        </ul>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger fade-in">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning fade-in">
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            </div>
        @endif
        
        @if(session('info'))
            <div class="alert alert-info fade-in">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif
        
        <!-- Page Header -->
        <div class="container">
            <div class="page-actions">
                @yield('page-actions')
            </div>
        </div>
        
        <!-- Page Content -->
        <div class="container">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Basculer le menu mobile
            const menuToggle = document.querySelector('.menu-toggle');
            const mobileNav = document.querySelector('.mobile-nav');
            
            if (menuToggle && mobileNav) {
                menuToggle.addEventListener('click', function() {
                    mobileNav.classList.toggle('active');
                });
                
                // Fermer le menu mobile en cliquant à l'extérieur
                document.addEventListener('click', function(event) {
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
                    button.addEventListener('click', function(e) {
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
                    document.addEventListener('click', function() {
                        menu.classList.remove('show');
                    });
                    
                    menu.addEventListener('click', function(e) {
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
                            fetch('{{ route("notifications.index") }}?limit=5')
                                .then(response => response.text())
                                .then(html => {
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(html, 'text/html');
                                    const notifications = doc.querySelector('.notifications-list');
                                    
                                    if (notifications) {
                                        document.querySelector('.notifications-list').innerHTML = notifications.innerHTML;
                                    }
                                })
                                .catch(error => console.error('Erreur:', error));
                        })
                        .catch(error => console.error('Erreur:', error));
                @endauth
            }
            
            // Mettre à jour les notifications toutes les 30 secondes
            setInterval(loadNotifications, 30000);
        });
    </script>
    
    <!-- Styles supplémentaires -->
    <style>
        /* Styles pour les menus déroulants */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            min-width: 280px;
            z-index: 1000;
            display: none;
            margin-top: 10px;
        }
        
        .dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }
        
        .notifications-menu {
            min-width: 350px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .dropdown-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dropdown-header h5 {
            margin: 0;
            font-size: 1rem;
        }
        
        .notifications-list {
            padding: 10px 0;
        }
        
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f8f9fa;
            transition: var(--transition);
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item.unread {
            background: #f8f9fa;
            font-weight: 500;
        }
        
        .notification-loading {
            text-align: center;
            padding: 20px;
            color: var(--gray-color);
        }
        
        .dropdown-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .profile-menu .user-info {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar-lg {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary-color), #3498db);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-fullname {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .user-email {
            display: block;
            font-size: 0.9rem;
            color: var(--gray-color);
            margin: 5px 0;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: var(--secondary-color);
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 10px 0;
        }
        
        .logout-form {
            width: 100%;
        }
        
        /* Menu mobile */
        .mobile-nav {
            display: none;
            background: white;
            position: fixed;
            top: var(--header-height);
            left: 0;
            right: 0;
            box-shadow: var(--shadow-md);
            z-index: 999;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .mobile-nav.active {
            display: block;
            max-height: 500px;
        }
        
        .mobile-nav-menu {
            list-style: none;
            padding: 20px;
        }
        
        .mobile-nav-menu li {
            margin-bottom: 10px;
        }
        
        .mobile-nav-menu a, .mobile-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: var(--dark-color);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            width: 100%;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .mobile-nav-menu a:hover, .mobile-logout:hover {
            background: #f8f9fa;
            color: var(--secondary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .page-actions {
                width: 100%;
            }
            
            .dropdown-menu {
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                margin: 0;
                border-radius: 0;
                max-height: 80vh;
                overflow-y: auto;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-nav {
                display: none !important;
            }
            
            .menu-toggle {
                display: none;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>