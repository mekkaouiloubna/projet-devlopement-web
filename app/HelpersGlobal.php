<?php

use App\Models\Notification;

if (!function_exists('format_bytes')) {
    /**
     * Formater les octets en format lisible
     */
    function format_bytes($bytes, $precision = 2)
    {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('format_bandwidth')) {
    /**
     * Formater la bande passante
     */
    function format_bandwidth($mbps)
    {
        if ($mbps >= 1000) {
            return ($mbps / 1000) . ' Gbps';
        }
        return $mbps . ' Mbps';
    }
}

if (!function_exists('get_status_badge')) {
    /**
     * Obtenir le badge HTML pour un statut
     */
    function get_status_badge($status)
    {
        $badges = [
            'available' => '<span class="badge badge-success">Disponible</span>',
            'reserved' => '<span class="badge badge-warning">Réservé</span>',
            'maintenance' => '<span class="badge badge-info">Maintenance</span>',
            'inactive' => '<span class="badge badge-secondary">Inactif</span>',
            'pending' => '<span class="badge badge-warning">En attente</span>',
            'approved' => '<span class="badge badge-success">Approuvé</span>',
            'rejected' => '<span class="badge badge-danger">Refusé</span>',
            'active' => '<span class="badge badge-primary">Actif</span>',
            'completed' => '<span class="badge badge-secondary">Terminé</span>',
            'cancelled' => '<span class="badge badge-dark">Annulé</span>',
            'open' => '<span class="badge badge-danger">Ouvert</span>',
            'in_progress' => '<span class="badge badge-warning">En cours</span>',
            'resolved' => '<span class="badge badge-success">Résolu</span>',
            'closed' => '<span class="badge badge-secondary">Fermé</span>',
        ];
        
        return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('get_priority_badge')) {
    /**
     * Obtenir le badge HTML pour une priorité
     */
    function get_priority_badge($priority)
    {
        $badges = [
            'low' => '<span class="badge badge-info">Basse</span>',
            'medium' => '<span class="badge badge-warning">Moyenne</span>',
            'high' => '<span class="badge badge-orange">Haute</span>',
            'critical' => '<span class="badge badge-danger">Critique</span>',
        ];
        
        return $badges[$priority] ?? '<span class="badge badge-secondary">' . ucfirst($priority) . '</span>';
    }
}

if (!function_exists('get_role_name')) {
    /**
     * Obtenir le nom du rôle en français
     */
    function get_role_name($role)
    {
        $roles = [
            'guest' => 'Invité',
            'user' => 'Utilisateur',
            'manager' => 'Responsable',
            'admin' => 'Administrateur',
        ];
        
        return $roles[$role] ?? ucfirst($role);
    }
}

if (!function_exists('get_user_type_name')) {
    /**
     * Obtenir le nom du type d'utilisateur en français
     */
    function get_user_type_name($type)
    {
        $types = [
            'engineer' => 'Ingénieur',
            'teacher' => 'Enseignant',
            'phd_student' => 'Doctorant',
            'other' => 'Autre',
        ];
        
        return $types[$type] ?? ucfirst($type);
    }
}

if (!function_exists('notify_user')) {
    /**
     * Créer une notification pour un utilisateur
     */
    function notify_user($userId, $type, $title, $message, $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);
    }
}

if (!function_exists('calculate_occupation_rate')) {
    /**
     * Calculer le taux d'occupation
     */
    function calculate_occupation_rate($total, $occupied)
    {
        if ($total == 0) {
            return 0;
        }
        return round(($occupied / $total) * 100, 2);
    }
}

if (!function_exists('format_date_fr')) {
    /**
     * Formater une date en français
     */
    function format_date_fr($date)
    {
        if (!$date) {
            return '';
        }
        
        $carbonDate = \Carbon\Carbon::parse($date);
        return $carbonDate->translatedFormat('d F Y à H:i');
    }
}

if (!function_exists('time_ago_fr')) {
    /**
     * Afficher le temps écoulé en français
     */
    function time_ago_fr($date)
    {
        if (!$date) {
            return '';
        }
        
        $carbonDate = \Carbon\Carbon::parse($date);
        return $carbonDate->diffForHumans();
    }
}