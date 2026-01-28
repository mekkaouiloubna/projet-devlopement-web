<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HistoryLogController;
use App\Http\Controllers\ResponsableController;
use App\Http\Middleware\AdminOrResponsableMiddleware;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Routes publiques (Accessibles sans authentification)
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Consultation des ressources (pour les invités)
Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('/resources/{id}', [ResourceController::class, 'show'])->name('resources.show');

/*
|--------------------------------------------------------------------------
| Routes protégées (Nécessitent une authentification)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Tableau de bord général
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->isResponsable()) {
            return redirect()->route('responsable.dashboard');
        } else {
            return view('dashboard.index');
        }
    })->name('dashboard');

    // Profil utilisateur
    Route::get('/profile', [UserController::class, 'show'])->name('profile')->defaults('id', 'me');
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes pour les Utilisateurs Internes
    |--------------------------------------------------------------------------
    */

    // Réservations
    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/create', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/{id}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::post('/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });

    // Conversations
    Route::post('/reservations/{reservation}/conversations', [ConversationController::class, 'store'])
        ->name('conversations.store');
    Route::post('/conversations/{id}/report', [ConversationController::class, 'report'])
        ->name('conversations.report');

    /*
    |--------------------------------------------------------------------------
    | Routes pour les Responsables
    | (Accès restreint avec middleware AdminOrResponsableMiddleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', AdminOrResponsableMiddleware::class])->group(function () {
        
        // Tableau de bord responsable
        Route::get('/responsable/dashboard', [ResponsableController::class, 'dashboard'])
            ->name('responsable.dashboard');
        
        // Gestion des ressources assignées
        Route::get('/responsable/resources', [ResponsableController::class, 'myResources'])
            ->name('responsable.resources');
        
        // Gestion des réservations
        Route::get('/responsable/reservations', [ResponsableController::class, 'reservations'])
            ->name('responsable.reservations');
        
        // Messages signalés
        Route::get('/responsable/reported-messages', [ResponsableController::class, 'reportedMessages'])
            ->name('responsable.reported-messages');
        
        // Actions sur les réservations
        Route::post('/reservations/{id}/approve', [ReservationController::class, 'approve'])
            ->name('reservations.approve');
        Route::post('/reservations/{id}/reject', [ReservationController::class, 'reject'])
            ->name('reservations.reject');
        
        // Transfert de responsabilité
        Route::post('/resources/{resource}/assign', [ResponsableController::class, 'assignResponsable'])
            ->name('responsable.assign');
        
        // Rapports d'utilisation
        Route::get('/resources/{resource}/usage-report', [ResponsableController::class, 'resourceUsageReport'])
            ->name('responsable.usage-report');
        
        // Suppression de messages
        Route::delete('/conversations/{id}', [ConversationController::class, 'destroy'])
            ->name('conversations.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes pour les Administrateurs
    | (Accès restreint avec middleware AdminMiddleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth',AdminMiddleware::class])->group(function () {
        
        // Tableau de bord admin
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/overview', [AdminController::class, 'systemOverview'])->name('admin.overview');
        
        // Gestion des utilisateurs
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{id}/update-role', [UserController::class, 'updateRole'])->name('users.update-role');
        
        // Gestion des catégories
        Route::prefix('admin/categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('admin.categories');
            Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('/{id}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });
        
        // Gestion des ressources (complet)
        Route::resource('admin/resources', ResourceController::class)->except(['index', 'show']);
        Route::get('/admin/resources', [AdminController::class, 'resources'])->name('admin.resources');
        Route::get('/resources/create', [ResourceController::class, 'create'])->name('resources.create');
        Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');
        
        // Maintenance
        Route::resource('maintenance', MaintenanceController::class);
        
        // Journaux d'activité
        Route::prefix('history')->group(function () {
            Route::get('/', [HistoryLogController::class, 'index'])->name('history.index');
            Route::get('/{id}', [HistoryLogController::class, 'show'])->name('history.show');
            Route::get('/export', [HistoryLogController::class, 'export'])->name('history.export');
        });
        
        // Rapports
        Route::get('/reports/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
        Route::post('/reports', [AdminController::class, 'generateReport'])->name('reports.generate.post');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes communes avec permissions spécifiques
    |--------------------------------------------------------------------------
    */

    // Gestion des ressources (création/modification selon rôle)
    Route::middleware(['auth', AdminOrResponsableMiddleware::class])->group(function () {
    Route::get('/resources/{resource}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
    Route::put('/resources/{resource}', [ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');
});


});

/*
|--------------------------------------------------------------------------
| Routes pour les tests (à supprimer en production)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/test', function () {
        return 'Test route - Application fonctionnelle!';
    });
}

/*
|--------------------------------------------------------------------------
| Fallback route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});