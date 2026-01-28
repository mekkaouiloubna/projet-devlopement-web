{{-- resources/views/reports/select.blade.php --}}
@extends('layouts.app')

@section('title', 'Générer un rapport')
@section('page-title', 'Sélection du rapport')

@section('content')
<div class="container">
    <div class="header-sec">
        <div class="header-sec-title">
            <h1><i class="fas fa-chart-bar me-2"></i>Générer un rapport</h1>
            <p>Sélectionnez le type de rapport et la période</p>
        </div>
    </div>

    <div class="section-card">
        <form action="{{ route('reports.generate.post') }}" method="POST">
            @csrf
            
            <div class="form-group mb-3">
                <label for="type" class="form-label font-wb">Type de rapport</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">Sélectionnez un type</option>
                    <option value="reservations">Réservations</option>
                    <option value="users">Utilisateurs</option>
                    <option value="resources">Ressources</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="start_date" class="form-label font-wb">Date de début</label>
                        <input type="date" name="start_date" id="start_date" 
                               class="form-control" 
                               max="{{ now()->format('Y-m-d') }}"
                               value="{{ now()->subMonth()->format('Y-m-d') }}"
                               required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="end_date" class="form-label font-wb">Date de fin</label>
                        <input type="date" name="end_date" id="end_date" 
                               class="form-control" 
                               max="{{ now()->format('Y-m-d') }}"
                               value="{{ now()->format('Y-m-d') }}"
                               required>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <div class="form-check">
                    <input type="checkbox" name="include_all" id="include_all" class="form-check-input">
                    <label for="include_all" class="form-check-label">
                        Inclure toutes les données (ignorer les dates)
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-chart-bar me-2"></i>Générer le rapport
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-outline">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>

    <!-- Suggestions rapides -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-bolt me-2"></i>Périodes prédéfinies</h3>
        </div>
        <div class="quick-periods">
            <a href="{{ route('reports.generate') }}?type=reservations&start_date={{ now()->subWeek()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
               class="period-card">
                <div class="period-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="period-content">
                    <h5>Cette semaine</h5>
                    <p>Réservations des 7 derniers jours</p>
                </div>
            </a>
            
            <a href="{{ route('reports.generate') }}?type=reservations&start_date={{ now()->subMonth()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
               class="period-card">
                <div class="period-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="period-content">
                    <h5>Ce mois</h5>
                    <p>Activité du dernier mois</p>
                </div>
            </a>
            
            <a href="{{ route('reports.generate') }}?type=reservations&start_date={{ now()->subMonths(3)->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
               class="period-card">
                <div class="period-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="period-content">
                    <h5>Trimestre</h5>
                    <p>Tendances sur 3 mois</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.quick-periods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    padding: 15px;
}

.period-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
    background: white;
}

.period-card:hover {
    border-color: #3498db;
    background-color: #f8f9fa;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.period-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    font-size: 1.2rem;
    transition: background-color 0.3s ease;
}

.period-card:hover .period-icon {
    background-color: #3498db;
    color: white;
}

.period-content h5 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.period-content p {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Définir la date max pour aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').max = today;
    document.getElementById('end_date').max = today;
    
    // Validation des dates
    document.querySelector('form').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (startDate > endDate) {
            e.preventDefault();
            alert('La date de début doit être antérieure à la date de fin.');
        }
    });
    
    // Cocher "Inclure toutes les données" désactive les champs de date
    document.getElementById('include_all').addEventListener('change', function() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.disabled = this.checked;
            if (this.checked) {
                input.value = '';
            }
        });
    });
});
</script>
@endsection