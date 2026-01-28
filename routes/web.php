<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportedMessageController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HistoryLogController;
use App\Http\Controllers\ResponsableController;
use App\Http\Middleware\AdminOrResponsableMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ResponsableMiddleware;
use App\Http\Controllers\ProfileController;


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
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clearRead');
        Route::get('/latest', [NotificationController::class, 'latestForDropdown'])->name('notifications.latest');

    });

    // Conversations
    Route::post('/reservations/{reservation}/conversations', [ConversationController::class, 'store'])
        ->name('conversations.store');
    Route::post('/conversations/{id}/report', [ConversationController::class, 'report'])
        ->name('conversations.report');

    /*
    |--------------------------------------------------------------------------
    | Routes pour les Responsables
    |--------------------------------------------------------------------------
    */
    Route::middleware([AdminOrResponsableMiddleware::class])->group(function () {

        // Transfert de responsabilité
        Route::post('/resources/{resource}/assign', [ResponsableController::class, 'assignResponsable'])
            ->name('responsable.assign');

        // Rapports d'utilisation
        Route::get('/resources/{resource}/usage-report', [ResponsableController::class, 'resourceUsageReport'])
            ->name('responsable.usage-report');

        // Suppression de messages
        Route::delete('/conversations/{id}', [ConversationController::class, 'destroy'])
            ->name('conversations.destroy');

        Route::get('/resources/{resource}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
        Route::put('/resources/{resource}', [ResourceController::class, 'update'])->name('resources.update');
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');

    });
    // Maintenance
    Route::resource('maintenance', MaintenanceController::class);
    // Réservations
    Route::prefix('reservations')->group(function () {
        Route::patch('/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::patch('/{id}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
        Route::patch('/{id}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
    });
    // Autres routes protégées peuvent être ajoutées ici
    Route::middleware([ResponsableMiddleware::class])->group(function () {
        // Gestion des réservations
        Route::get('/responsable/reservations', [ResponsableController::class, 'reservations'])->name('responsable.reservations');
        Route::get('/responsable/dashboard', [ResponsableController::class, 'dashboard'])->name('responsable.dashboard');
        Route::get('/responsable/resources', [ResponsableController::class, 'myResources'])->name('responsable.resources');
        // Messages signalés
        Route::get('/responsable/reported-messages', [ResponsableController::class, 'reportedMessages'])->name('responsable.reported-messages');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes pour les Administrateurs
    | (Accès restreint avec middleware AdminMiddleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('users', userController::class);

        // Gestion des utilisateurs (Admin uniquement)
        Route::prefix('admin/users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/{user}/edit-role', [UserController::class, 'editRole'])->name('admin.users.edit-role');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
            Route::post('/{user}/update-role', [UserController::class, 'updateRole'])->name('admin.users.update-role');
            Route::post('/{user}/approve-registration', [UserController::class, 'approveRegistration'])->name('admin.users.approve-registration');
            Route::post('/{user}/reject-registration', [UserController::class, 'rejectRegistration'])->name('admin.users.reject-registration');
        });

        // Tableau de bord admin
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.admin');
        Route::get('/admin/overview', [AdminController::class, 'systemOverview'])->name('admin.overview');

        // Gestion des utilisateurs
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{id}/update-role', [UserController::class, 'updateRole'])->name('users.update-role');

        // Gestion des catégories
        Route::resource('admin/categories', CategoryController::class);

        // Gestion des ressources (complet)
        Route::resource('admin/resources', ResourceController::class)->except(['index', 'show']);
        Route::get('/admin/resources', [AdminController::class, 'resources'])->name('admin.resources');

        // Journaux d'activité
        Route::prefix('history')->group(function () {
            Route::get('/', [HistoryLogController::class, 'index'])->name('history.index');
            Route::get('/export', [HistoryLogController::class, 'export'])->name('history.export');
        });

        // Rapports
        Route::get('/reports/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
        Route::post('/reports', [AdminController::class, 'generateReport'])->name('reports.generate.post');
        Route::get('/history-logs', [HistoryLogController::class, 'index'])->name('history-logs.index');
        Route::get('/history-logs/stats', [HistoryLogController::class, 'stats'])->name('history.stats');
        Route::get('/history-logs/export', [HistoryLogController::class, 'exportCsv'])->name('history-logs.export');

    });

    Route::get('/messages-signales/creer', [ReportedMessageController::class, 'create'])->name('reported-messages.create');
    Route::post('/messages-signales', [ReportedMessageController::class, 'store'])->name('reported-messages.store');
    Route::get('/responsable/reported-messages', [ReportedMessageController::class, 'index'])->name('reported-messages.index');
    Route::post('/messages-signales/{id}/marquer-comme-lu', [ReportedMessageController::class, 'markAsRead'])->name('reported-messages.markAsRead');
    Route::delete('/messages-signales/{id}', [ReportedMessageController::class, 'destroy'])->name('reported-messages.destroy');
    Route::resource('reservations', ReservationController::class);

    Route::post('/reservations/{reservation}/comment', [ReservationController::class, 'addComment'])->name('reservations.addComment');

    Route::prefix('profile')->group(function () {
        Route::get('/{user}', [UserController::class, 'show'])->name('profile.show');
        Route::put('/', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::get('/', [ProfileController::class, 'show'])->name('profile');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.delete');
    });
});