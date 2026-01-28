@extends('layouts.app')

@section('title', 'Créer un message signalé')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">
        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
        Créer un message signalé
    </h1>

    <!-- Message de succès -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Formulaire -->
    <form action="{{ route('reported-messages.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf

        <!-- Sélection de la ressource -->
        <div class="mb-4">
            <label for="resource_id" class="block text-gray-700 text-sm font-bold mb-2">
                <i class="fas fa-cube text-blue-500 mr-1"></i>
                Ressource concernée
            </label>
            <select name="resource_id" id="resource_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">-- Sélectionner une ressource --</option>
                @foreach($resources as $resource)
                    <option value="{{ $resource->id }}">{{ $resource->nom }}</option>
                @endforeach
            </select>
            @error('resource_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Message -->
        <div class="mb-4">
            <label for="message" class="block text-gray-700 text-sm font-bold mb-2">
                <i class="fas fa-comment-alt text-green-500 mr-1"></i>
                Message
            </label>
            <textarea name="message" id="message" rows="4" placeholder="Décrivez votre problème..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            @error('message')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Bouton -->
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-paper-plane mr-1"></i>
                Envoyer
            </button>
        </div>
    </form>
</div>
@endsection
