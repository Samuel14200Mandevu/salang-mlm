@extends('layouts.app')

@section('title', 'Contact - Salang MLM')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Contactez-nous</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
            <p class="text-gray-600 mb-6">
                Vous avez une question, une suggestion ou un problème ? N'hésitez pas à nous contacter.
            </p>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Email</h3>
                    <p class="text-gray-600">contact@salang.com</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Téléphone</h3>
                    <p class="text-gray-600">+225 07 00 00 00 00</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Adresse</h3>
                    <p class="text-gray-600">Abidjan, Côte d'Ivoire</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Heures d'ouverture</h3>
                    <p class="text-gray-600">Lun - Ven: 8h - 18h</p>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Votre nom</label>
                        <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Votre email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                    </div>
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 px-6 rounded-lg transition">
                        Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection